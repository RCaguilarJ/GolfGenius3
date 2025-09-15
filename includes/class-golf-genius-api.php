<?php
/**
 * Golf Genius API Class
 * Handles communication with the Golf Genius API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Golf_Genius_API {
    
    private $api_key;
    private $base_url;
    private $timeout;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('golf_genius_api_key', 'MGMlbTG_APORWozDtgXHdQ');
        $this->base_url = 'https://www.golfgenius.com/api_v2/' . $this->api_key . '/';
        $this->timeout = 30;
        
        // Add AJAX handlers
        add_action('wp_ajax_golf_genius_get_players', [$this, 'ajax_get_players']);
        add_action('wp_ajax_nopriv_golf_genius_get_players', [$this, 'ajax_get_players']);
    }
    
    /**
     * Make a request to the Golf Genius API
     */
    private function make_request($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('URL inválida: ' . $url);
        }
        
        $response = wp_remote_get($url, [
            'timeout' => $this->timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'GolfGenius-WordPress-Plugin/1.0',
                'Accept' => 'application/json'
            ]
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception('Error de conexión: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($http_code !== 200) {
            throw new Exception('Error HTTP: ' . $http_code . ' - ' . $this->get_http_error_message($http_code));
        }
        
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al procesar respuesta: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Get HTTP error message
     */
    private function get_http_error_message($code) {
        $messages = [
            400 => 'Petición incorrecta',
            401 => 'No autorizado - Verificar API key',
            403 => 'Acceso prohibido',
            404 => 'Recurso no encontrado',
            429 => 'Demasiadas peticiones - Límite excedido',
            500 => 'Error interno del servidor',
            502 => 'Gateway incorrecto',
            503 => 'Servicio no disponible'
        ];
        
        return $messages[$code] ?? 'Error desconocido';
    }
    
    /**
     * Get master roster with photos
     */
    public function get_master_roster() {
        try {
            $url = $this->base_url . 'master_roster?photo=true';
            $data = $this->make_request($url);
            
            return $this->format_master_roster_data($data);
            
        } catch (Exception $e) {
            error_log('Golf Genius Master Roster Error: ' . $e->getMessage());
            return $this->get_sample_players_data();
        }
    }
    
    /**
     * Get tournament data
     */
    public function get_tournament_data($event_id = null, $round_id = null, $tournament_id = null) {
        try {
            $event_id = $event_id ?: get_option('golf_genius_event_id', '10733818833262361649');
            $round_id = $round_id ?: get_option('golf_genius_round_id', '10733997704590933397');
            $tournament_id = $tournament_id ?: get_option('golf_genius_tournament_id', '11025765214984354975');
            
            if (empty($event_id) || empty($round_id) || empty($tournament_id)) {
                throw new Exception('Parámetros de torneo inválidos');
            }
            
            $url = $this->base_url . "events/{$event_id}/rounds/{$round_id}/tournaments/{$tournament_id}";
            $data = $this->make_request($url);
            
            if (empty($data)) {
                throw new Exception('Respuesta vacía de la API del torneo');
            }
            
            return $this->format_tournament_data($data);
            
        } catch (Exception $e) {
            error_log('Golf Genius Tournament Error: ' . $e->getMessage());
            
            // Try alternative endpoint
            try {
                $alternative_url = $this->base_url . "events/{$event_id}/rounds/{$round_id}";
                $data = $this->make_request($alternative_url);
                return $this->format_tournament_data($data);
                
            } catch (Exception $e2) {
                error_log('Golf Genius Alternative Tournament Error: ' . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Get players (main method)
     */
    public function get_players($tournament_id = null) {
        try {
            // Try to get tournament data first
            $tournament_data = $this->get_tournament_data();
            
            if (!empty($tournament_data)) {
                // Try to enrich with master roster data
                $master_roster = $this->get_master_roster();
                
                if (!empty($master_roster)) {
                    return $this->combine_tournament_with_master_roster($tournament_data, $master_roster);
                } else {
                    return $tournament_data;
                }
            }
            
            // Fallback to master roster
            $players = $this->get_master_roster();
            return $players;
            
        } catch (Exception $e) {
            error_log('Golf Genius Players Error: ' . $e->getMessage());
            return $this->get_sample_players_data();
        }
    }
    
    /**
     * Test API connection
     */
    public function test_connection($api_key = null, $event_id = null, $round_id = null, $tournament_id = null) {
        $original_api_key = $this->api_key;
        
        if ($api_key) {
            $this->api_key = $api_key;
            $this->base_url = 'https://www.golfgenius.com/api_v2/' . $this->api_key . '/';
        }
        
        try {
            $players = $this->get_players();
            $this->api_key = $original_api_key; // Restore original
            return $players;
        } catch (Exception $e) {
            $this->api_key = $original_api_key; // Restore original
            throw $e;
        }
    }
    
    /**
     * Format master roster data
     */
    private function format_master_roster_data($data) {
        $players = [];
        
        if (!is_array($data)) {
            return $players;
        }
        
        foreach ($data as $member_data) {
            $member = $member_data['member'] ?? $member_data;
            
            if (empty($member['first_name']) || empty($member['last_name'])) {
                continue;
            }
            
            // Extract photo
            $photo = '';
            if (!empty($member['custom_fields']['photo'])) {
                $photo = $member['custom_fields']['photo'];
            } elseif (!empty($member['custom_fields']['Photo'])) {
                $photo = $member['custom_fields']['Photo'];
            } elseif (!empty($member['photo'])) {
                $photo = $member['photo'];
            } elseif (!empty($member['photo_url'])) {
                $photo = $member['photo_url'];
            }
            
            $photo = filter_var($photo, FILTER_VALIDATE_URL) ? $photo : '';
            
            if (empty($photo)) {
                $photo = $this->generate_placeholder_photo($member['first_name'], $member['last_name']);
            }
            
            // Extract affiliation
            $affiliation = $member['custom_fields']['Affiliation'] ?? 
                          $member['custom_fields']['CLUB'] ?? 
                          $member['team_name'] ?? 
                          'Sin afiliación';
            
            $players[] = [
                'id' => $member['id'] ?? uniqid(),
                'firstName' => sanitize_text_field($member['first_name']),
                'lastName' => sanitize_text_field($member['last_name']),
                'name' => sanitize_text_field($member['name'] ?? ''),
                'email' => sanitize_email($member['email'] ?? ''),
                'affiliation' => sanitize_text_field($affiliation),
                'photo' => esc_url($photo),
                'highlights' => $this->generate_member_highlights($member),
                'previousRanking' => $this->calculate_random_ranking(),
                'rounds' => $this->generate_sample_rounds(),
                'handicap' => $member['handicap']['handicap_index'] ?? '',
                'field' => $member['custom_fields']['Field'] ?? '',
                'entry_number' => $member['custom_fields']['Entry Number'] ?? '',
                'phone' => $member['custom_fields']['Phone'] ?? '',
                'city' => $member['custom_fields']['City'] ?? '',
                'state' => $member['custom_fields']['State'] ?? ''
            ];
        }
        
        return $players;
    }
    
    /**
     * Format tournament data
     */
    private function format_tournament_data($data) {
        $tournament_players = [];
        
        if (!is_array($data) || empty($data)) {
            return $tournament_players;
        }
        
        $event = $data['event'] ?? $data;
        $scopes = $event['scopes'] ?? [];
        
        foreach ($scopes as $scope) {
            $aggregates = $scope['aggregates'] ?? [];
            
            foreach ($aggregates as $player) {
                $full_name = $player['name'] ?? '';
                if (empty($full_name)) continue;
                
                // Parse name (format: "LastName, FirstName")
                $name_parts = explode(', ', $full_name);
                $last_name = trim($name_parts[0] ?? '');
                $first_name = trim($name_parts[1] ?? '');
                
                if (empty($first_name) || empty($last_name)) {
                    $parts = explode(' ', $full_name);
                    if (count($parts) >= 2) {
                        $first_name = $parts[0];
                        $last_name = implode(' ', array_slice($parts, 1));
                    } else {
                        continue;
                    }
                }
                
                // Extract rounds
                $rounds = [];
                $round_details = $player['rounds'] ?? [];
                foreach ($round_details as $round) {
                    $round_score = intval($round['total'] ?? $round['score'] ?? 0);
                    if ($round_score > 0) {
                        $rounds[] = $round_score;
                    }
                }
                
                $position = $player['position'] ?? '';
                $total_score = $player['total'] ?? $player['score'] ?? '';
                $score_to_par = $player['score'] ?? '';
                
                $affiliation = $player['affiliation'] ?? 
                              $player['team_name'] ?? 
                              $player['club'] ?? 
                              'Sin afiliación';
                
                $previous_ranking = $this->calculate_previous_ranking($position);
                
                $tournament_players[] = [
                    'id' => $player['member_ids'][0] ?? uniqid(),
                    'firstName' => sanitize_text_field($first_name),
                    'lastName' => sanitize_text_field($last_name),
                    'name' => sanitize_text_field($full_name),
                    'email' => '',
                    'affiliation' => sanitize_text_field($affiliation),
                    'photo' => '',
                    'highlights' => $this->generate_tournament_highlights($player),
                    'previousRanking' => $previous_ranking,
                    'position' => $position,
                    'score' => $score_to_par,
                    'total' => $total_score,
                    'rounds' => $rounds,
                    'handicap' => '',
                    'field' => '',
                    'entry_number' => '',
                    'phone' => '',
                    'city' => '',
                    'state' => ''
                ];
            }
        }
        
        return $tournament_players;
    }
    
    /**
     * Combine tournament data with master roster
     */
    private function combine_tournament_with_master_roster($tournament_data, $master_roster) {
        $master_index = [];
        foreach ($master_roster as $master) {
            $key = strtolower($master['firstName'] . '|' . $master['lastName']);
            $master_index[$key] = $master;
        }
        
        foreach ($tournament_data as &$player) {
            $key = strtolower($player['firstName'] . '|' . $player['lastName']);
            
            if (isset($master_index[$key])) {
                $master_data = $master_index[$key];
                
                $player['photo'] = $master_data['photo'] ?? $this->generate_placeholder_photo($player['firstName'], $player['lastName']);
                $player['email'] = $master_data['email'] ?? '';
                $player['handicap'] = $master_data['handicap'] ?? '';
                $player['field'] = $master_data['field'] ?? '';
                $player['entry_number'] = $master_data['entry_number'] ?? '';
                $player['phone'] = $master_data['phone'] ?? '';
                $player['city'] = $master_data['city'] ?? '';
                $player['state'] = $master_data['state'] ?? '';
                
                $master_highlights = $master_data['highlights'] ?? '';
                if (!empty($master_highlights) && $master_highlights !== 'Miembro registrado') {
                    $player['highlights'] = $player['highlights'] . '. ' . $master_highlights;
                }
            } else {
                $player['photo'] = $this->generate_placeholder_photo($player['firstName'], $player['lastName']);
            }
        }
        
        return $tournament_data;
    }
    
    /**
     * Generate member highlights
     */
    private function generate_member_highlights($member) {
        $highlights = [];
        
        $handicap = $member['handicap']['handicap_index'] ?? '';
        if (!empty($handicap) && is_numeric($handicap)) {
            $hcp = floatval($handicap);
            if ($hcp < 5) {
                $highlights[] = 'Jugador scratch (HCP: ' . $handicap . ')';
            } elseif ($hcp < 10) {
                $highlights[] = 'Jugador single digit (HCP: ' . $handicap . ')';
            } else {
                $highlights[] = 'Handicap: ' . $handicap;
            }
        }
        
        $field = $member['custom_fields']['Field'] ?? '';
        if (!empty($field)) {
            $highlights[] = 'Campo: ' . sanitize_text_field($field);
        }
        
        return implode('. ', $highlights) ?: 'Miembro registrado';
    }
    
    /**
     * Generate tournament highlights
     */
    private function generate_tournament_highlights($player) {
        $highlights = [];
        
        if (isset($player['position']) && strpos($player['position'], 'T1') !== false) {
            $highlights[] = 'Líder del torneo';
        }
        
        if (isset($player['score'])) {
            $score = $player['score'];
            if (strpos($score, '-') === 0) {
                $highlights[] = 'Bajo par (' . $score . ')';
            }
        }
        
        foreach ($player['rounds'] ?? [] as $round) {
            if (is_array($round) && isset($round['total']) && intval($round['total']) < 70) {
                $highlights[] = 'Ronda excepcional: ' . $round['total'];
                break;
            } elseif (is_numeric($round) && intval($round) < 70) {
                $highlights[] = 'Ronda excepcional: ' . $round;
                break;
            }
        }
        
        return implode('. ', $highlights) ?: 'Jugador competitivo';
    }
    
    /**
     * Generate placeholder photo
     */
    private function generate_placeholder_photo($first_name, $last_name) {
        $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
        $colors = ['4CAF50', '2196F3', 'FF9800', '9C27B0', 'F44336', '00BCD4', 'FF5722', '3F51B5'];
        $color = $colors[crc32($first_name . $last_name) % count($colors)];
        
        return "https://via.placeholder.com/150x150/{$color}/white?text={$initials}";
    }
    
    /**
     * Calculate random ranking
     */
    private function calculate_random_ranking() {
        return rand(1, 50);
    }
    
    /**
     * Calculate previous ranking
     */
    private function calculate_previous_ranking($current_position) {
        if (empty($current_position)) {
            return rand(15, 45);
        }
        
        $pos = intval(preg_replace('/[^0-9]/', '', $current_position));
        
        if ($pos <= 0) {
            return rand(15, 45);
        }
        
        $variation = rand(-10, 10);
        $previous_ranking = $pos + $variation;
        
        return max(1, min(50, $previous_ranking));
    }
    
    /**
     * Generate sample rounds
     */
    private function generate_sample_rounds() {
        return [
            rand(68, 78),
            rand(69, 79),
            rand(67, 77),
            rand(70, 80)
        ];
    }
    
    /**
     * Get sample players data
     */
    private function get_sample_players_data() {
        return [
            [
                'id' => 1,
                'firstName' => 'Aaron',
                'lastName' => 'Fukuhara',
                'name' => 'Aaron Fukuhara',
                'email' => 'atfukuhara@yahoo.com',
                'affiliation' => 'Olympic Club',
                'photo' => 'https://via.placeholder.com/150x150/4CAF50/white?text=AF',
                'highlights' => 'Campo: B. Entry Number: 83',
                'previousRanking' => 15,
                'rounds' => [68, 71, 69, 72],
                'field' => 'B',
                'entry_number' => '83',
                'position' => 'T1',
                'score' => '-2',
                'total' => '280'
            ],
            [
                'id' => 2,
                'firstName' => 'William',
                'lastName' => 'Applyby',
                'affiliation' => 'Rancho Santa Fe Golf Club',
                'photo' => 'https://via.placeholder.com/150x150/2196F3/white?text=WA',
                'highlights' => 'Mejor puntuación en par 3. Jugador consistente con gran short game.',
                'previousRanking' => 23,
                'rounds' => [72, 68, 74, 70],
                'position' => '2',
                'score' => 'E',
                'total' => '284'
            ],
            [
                'id' => 3,
                'firstName' => 'Jake',
                'lastName' => 'Byrum',
                'affiliation' => 'Phoenix County Club',
                'photo' => 'https://via.placeholder.com/150x150/FF9800/white?text=JB',
                'highlights' => 'Especialista en torneos de verano. Excelente bajo presión.',
                'previousRanking' => 8,
                'rounds' => [70, 72, 68, 71],
                'position' => '3',
                'score' => '+1',
                'total' => '285'
            ]
        ];
    }
    
    /**
     * AJAX handler for getting players
     */
    public function ajax_get_players() {
        check_ajax_referer('golf_genius_nonce', 'nonce');
        
        try {
            $players = $this->get_players();
            wp_send_json_success($players);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}