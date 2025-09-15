<?php
/**
 * API Proxy para Golf Genius (Versión Mejorada)
 * Este archivo actúa como proxy entre la aplicación frontend y la API de Golf Genius
 */

// Configuración de errores para producción
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 0);

// Configuración CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Manejo de preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuración de la API de Golf Genius
class GolfGeniusAPI {
    private $apiKey;
    private $baseUrl;
    private $timeout;
    
    public function __construct() {
        // Cargar API key desde variable de entorno o configuración
        $this->apiKey = $_ENV['GOLF_GENIUS_API_KEY'] ?? 'MGMlbTG_APORWozDtgXHdQ';
        $this->baseUrl = 'https://www.golfgenius.com/api_v2/' . $this->apiKey . '/';
        $this->timeout = 30;
    }
    
    /**
     * Realiza una petición GET a la API de Golf Genius
     */
    private function makeRequest($url) {
        // Validar URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('URL inválida: ' . $url);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: GolfGenius-Client/1.0',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false, // Deshabilitado para desarrollo local
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        // Logging mejorado
        if ($error) {
            error_log("Golf Genius API cURL Error: " . $error . " | URL: " . $url);
            throw new Exception('Error de conexión: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("Golf Genius API HTTP Error: " . $httpCode . " | Response: " . substr($response, 0, 500));
            throw new Exception('Error HTTP: ' . $httpCode . ' - ' . $this->getHttpErrorMessage($httpCode));
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Golf Genius API JSON Error: " . json_last_error_msg() . " | Response: " . substr($response, 0, 500));
            throw new Exception('Error al procesar respuesta: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Obtiene mensaje de error HTTP amigable
     */
    private function getHttpErrorMessage($code) {
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
     * Obtiene el roster master con fotos
     */
    public function getMasterRoster() {
        try {
            $url = $this->baseUrl . 'master_roster?photo=true';
            $data = $this->makeRequest($url);
            
            return $this->formatMasterRosterData($data);
            
        } catch (Exception $e) {
            error_log('Golf Genius Master Roster Error: ' . $e->getMessage());
            // En desarrollo, usar datos de ejemplo
            return $this->getSamplePlayersData();
        }
    }
    
    /**
     * Obtiene datos de un torneo específico
     */
    public function getTournamentData($eventId, $roundId, $tournamentId) {
        try {
            // Validar parámetros
            if (empty($eventId) || empty($roundId) || empty($tournamentId)) {
                throw new Exception('Parámetros de torneo inválidos');
            }
            
            // Construir URL de la API específica
            $url = $this->baseUrl . "events/{$eventId}/rounds/{$roundId}/tournaments/{$tournamentId}";
            
            // Log para debugging
            error_log("Golf Genius Tournament API URL: " . $url);
            
            $data = $this->makeRequest($url);
            
            if (empty($data)) {
                throw new Exception('Respuesta vacía de la API del torneo');
            }
            
            return $this->formatTournamentData($data);
            
        } catch (Exception $e) {
            error_log('Golf Genius Tournament Error: ' . $e->getMessage());
            
            // Intentar con endpoint alternativo si falla
            try {
                $alternativeUrl = $this->baseUrl . "events/{$eventId}/rounds/{$roundId}";
                error_log("Intentando URL alternativa: " . $alternativeUrl);
                
                $data = $this->makeRequest($alternativeUrl);
                return $this->formatTournamentData($data);
                
            } catch (Exception $e2) {
                error_log('Golf Genius Alternative Tournament Error: ' . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Obtiene la lista de jugadores (método principal) - Prioriza datos del torneo
     */
    public function getPlayers($tournamentId = null) {
        try {
            // Intentar obtener datos del torneo específico primero (datos más precisos)
            $tournamentData = $this->getTournamentData(
                '10733818833262361649', // Event ID 
                '10733997704590933397', // Round ID 
                '11025765214984354975'  // Tournament ID 
            );
            
            // Si tenemos datos del torneo, usarlos como base
            if (!empty($tournamentData)) {
                // Intentar enriquecer con datos del master roster (fotos, etc.)
                $masterRoster = $this->getMasterRoster();
                
                if (!empty($masterRoster)) {
                    // Combinar: datos del torneo + fotos/info del master roster
                    return $this->combineTournamentWithMasterRoster($tournamentData, $masterRoster);
                } else {
                    // Solo datos del torneo
                    return $tournamentData;
                }
            }
            
            // Fallback: usar master roster si el torneo falla
            $players = $this->getMasterRoster();
            return $players;
            
        } catch (Exception $e) {
            error_log('Golf Genius Players Error: ' . $e->getMessage());
            return $this->getSamplePlayersData();
        }
    }
    
    /**
     * Obtiene las opciones relevantes para la tabla
     */
    public function getTableOptions() {
        return [
            'photo',
            'firstName',
            'lastName',
            'affiliation',
            'position',
            'score',
            'rounds',
            'highlights',
            'previousRanking',
            'email',
            'handicap',
            'field',
            'entry_number',
            'phone',
            'city',
            'state'
        ];
    }
    
    /**
     * Formatea los datos del master roster
     */
    private function formatMasterRosterData($data) {
        $players = [];
        
        if (!is_array($data)) {
            return $players;
        }
        
        foreach ($data as $memberData) {
            $member = $memberData['member'] ?? $memberData;
            
            // Validar datos mínimos
            if (empty($member['first_name']) || empty($member['last_name'])) {
                continue;
            }
            
            // Extraer y limpiar foto - revisar múltiples ubicaciones posibles
            $photo = '';
            
            // Opción 1: custom_fields['photo']
            if (!empty($member['custom_fields']['photo'])) {
                $photo = $member['custom_fields']['photo'];
            }
            // Opción 2: custom_fields['Photo'] (con mayúscula)
            elseif (!empty($member['custom_fields']['Photo'])) {
                $photo = $member['custom_fields']['Photo'];
            }
            // Opción 3: directamente en member['photo']
            elseif (!empty($member['photo'])) {
                $photo = $member['photo'];
            }
            // Opción 4: member['photo_url']
            elseif (!empty($member['photo_url'])) {
                $photo = $member['photo_url'];
            }
            // Opción 5: custom_fields['IMAGE'] o 'image'
            elseif (!empty($member['custom_fields']['IMAGE'])) {
                $photo = $member['custom_fields']['IMAGE'];
            }
            elseif (!empty($member['custom_fields']['image'])) {
                $photo = $member['custom_fields']['image'];
            }
            
            // Validar que sea una URL válida
            $photo = filter_var($photo, FILTER_VALIDATE_URL) ? $photo : '';
            
            // Generar avatar con iniciales si no hay foto disponible
            if (empty($photo)) {
                $firstNameInitial = !empty($member['firstName']) ? strtoupper($member['firstName'][0]) : '';
                $lastNameInitial = !empty($member['lastName']) ? strtoupper($member['lastName'][0]) : '';
                $photo = $firstNameInitial . $lastNameInitial;
            }
            
            // Extraer afiliación
            $affiliation = $member['custom_fields']['Affiliation'] ?? 
                          $member['custom_fields']['CLUB'] ?? 
                          $member['team_name'] ?? 
                          'Sin afiliación';
            
            // Limpiar y validar datos
            $players[] = [
                'id' => $member['id'] ?? uniqid(),
                'firstName' => htmlspecialchars($member['first_name'], ENT_QUOTES, 'UTF-8'),
                'lastName' => htmlspecialchars($member['last_name'], ENT_QUOTES, 'UTF-8'),
                'name' => htmlspecialchars($member['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                'email' => filter_var($member['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '',
                'affiliation' => htmlspecialchars($affiliation, ENT_QUOTES, 'UTF-8'),
                'photo' => $photo ?: $this->generatePlaceholderPhoto($member['first_name'], $member['last_name']),
                'highlights' => $this->generateMemberHighlights($member),
                'previousRanking' => $this->calculateRandomRanking(),
                'rounds' => $this->generateSampleRounds(),
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
     * Formatea los datos del torneo
     */
    private function formatTournamentData($data) {
        $tournamentPlayers = [];
        
        if (!is_array($data) || empty($data)) {
            return $tournamentPlayers;
        }
        
        // Navegar por la estructura de la API de Golf Genius
        $event = $data['event'] ?? $data;
        $scopes = $event['scopes'] ?? [];
        
        foreach ($scopes as $scope) {
            $aggregates = $scope['aggregates'] ?? [];
            
            foreach ($aggregates as $player) {
                // Extraer nombre completo
                $fullName = $player['name'] ?? '';
                if (empty($fullName)) continue;
                
                // Parsear el nombre (formato: "LastName, FirstName")
                $nameParts = explode(', ', $fullName);
                $lastName = trim($nameParts[0] ?? '');
                $firstName = trim($nameParts[1] ?? '');
                
                // Si no pudimos parsear, intentar formato alternativo
                if (empty($firstName) || empty($lastName)) {
                    $parts = explode(' ', $fullName);
                    if (count($parts) >= 2) {
                        $firstName = $parts[0];
                        $lastName = implode(' ', array_slice($parts, 1));
                    } else {
                        continue; // Saltar si no podemos determinar el nombre
                    }
                }
                
                // Extraer puntuaciones por ronda
                $rounds = [];
                $roundDetails = $player['rounds'] ?? [];
                foreach ($roundDetails as $round) {
                    $roundScore = intval($round['total'] ?? $round['score'] ?? 0);
                    if ($roundScore > 0) {
                        $rounds[] = $roundScore;
                    }
                }
                
                // Extraer posición y puntuación total
                $position = $player['position'] ?? '';
                $totalScore = $player['total'] ?? $player['score'] ?? '';
                $scoreToPar = $player['score'] ?? '';
                
                // Extraer afiliación/club
                $affiliation = $player['affiliation'] ?? 
                              $player['team_name'] ?? 
                              $player['club'] ?? 
                              'Sin afiliación';
                
                // Calcular ranking anterior simulado basado en posición actual
                $previousRanking = $this->calculatePreviousRanking($position);
                
                $tournamentPlayers[] = [
                    'id' => $player['member_ids'][0] ?? uniqid(),
                    'firstName' => htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'),
                    'lastName' => htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'),
                    'name' => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
                    'email' => '',
                    'affiliation' => htmlspecialchars($affiliation, ENT_QUOTES, 'UTF-8'),
                    'photo' => '', // Se llenará al combinar con master roster
                    'highlights' => $this->generateTournamentHighlights($player),
                    'previousRanking' => $previousRanking,
                    'position' => $position,
                    'score' => $scoreToPar,
                    'total' => $totalScore,
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
        
        return $tournamentPlayers;
    }
    
    /**
     * Combina datos del master roster con datos del torneo
     */
    private function combineMasterRosterWithTournament($masterRoster, $tournamentData) {
        foreach ($masterRoster as &$player) {
            // Buscar el jugador en los datos del torneo
            foreach ($tournamentData as $tournamentPlayer) {
                if ($player['firstName'] === $tournamentPlayer['firstName'] && 
                    $player['lastName'] === $tournamentPlayer['lastName']) {
                    
                    // Combinar datos
                    $player['position'] = $tournamentPlayer['position'];
                    $player['score'] = $tournamentPlayer['score'];
                    $player['total'] = $tournamentPlayer['total'];
                    $player['rounds'] = $tournamentPlayer['rounds'];
                    $player['highlights'] = $tournamentPlayer['highlights'];
                    break;
                }
            }
        }
        
        return $masterRoster;
    }
    
    /**
     * Combina datos del torneo con información del master roster (fotos y datos adicionales)
     */
    private function combineTournamentWithMasterRoster($tournamentData, $masterRoster) {
        // Crear un índice del master roster por nombre para búsqueda rápida
        $masterIndex = [];
        foreach ($masterRoster as $master) {
            $key = strtolower($master['firstName'] . '|' . $master['lastName']);
            $masterIndex[$key] = $master;
        }
        
        // Enriquecer datos del torneo con información del master roster
        foreach ($tournamentData as &$player) {
            $key = strtolower($player['firstName'] . '|' . $player['lastName']);
            
            if (isset($masterIndex[$key])) {
                $masterData = $masterIndex[$key];
                
                // Mantener datos del torneo como principales, agregar info del master roster
                $player['photo'] = $masterData['photo'] ?? $this->generatePlaceholderPhoto($player['firstName'], $player['lastName']);
                $player['email'] = $masterData['email'] ?? '';
                $player['handicap'] = $masterData['handicap'] ?? '';
                $player['field'] = $masterData['field'] ?? '';
                $player['entry_number'] = $masterData['entry_number'] ?? '';
                $player['phone'] = $masterData['phone'] ?? '';
                $player['city'] = $masterData['city'] ?? '';
                $player['state'] = $masterData['state'] ?? '';
                
                // Combinar highlights del torneo con datos del master roster
                $masterHighlights = $masterData['highlights'] ?? '';
                if (!empty($masterHighlights) && $masterHighlights !== 'Miembro registrado') {
                    $player['highlights'] = $player['highlights'] . '. ' . $masterHighlights;
                }
            } else {
                // Si no encontramos en master roster, generar foto placeholder
                $player['photo'] = $this->generatePlaceholderPhoto($player['firstName'], $player['lastName']);
            }
        }
        
        return $tournamentData;
    }
    
    /**
     * Genera highlights para miembros del master roster
     */
    private function generateMemberHighlights($member) {
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
            $highlights[] = 'Campo: ' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8');
        }
        
        $proAm = $member['custom_fields']['Pro or Amateur'] ?? '';
        if (!empty($proAm)) {
            $highlights[] = ucfirst(strtolower(htmlspecialchars($proAm, ENT_QUOTES, 'UTF-8')));
        }
        
        return implode('. ', $highlights) ?: 'Miembro registrado';
    }
    
    /**
     * Genera highlights para jugadores del torneo
     */
    private function generateTournamentHighlights($player) {
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
        
        // Verificar si tiene una ronda especialmente buena
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
     * Genera una foto placeholder atractiva para un jugador
     */
    private function generatePlaceholderPhoto($firstName, $lastName) {
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        
        // Colores atractivos para los placeholders
        $colors = ['4CAF50', '2196F3', 'FF9800', '9C27B0', 'F44336', '00BCD4', 'FF5722', '3F51B5'];
        $color = $colors[crc32($firstName . $lastName) % count($colors)];
        
        return "https://via.placeholder.com/150x150/{$color}/white?text={$initials}";
    }
    
    /**
     * Genera ranking aleatorio para miembros
     */
    private function calculateRandomRanking() {
        return rand(1, 50);
    }
    
    /**
     * Calcula un ranking anterior simulado basado en la posición actual
     */
    private function calculatePreviousRanking($currentPosition) {
        if (empty($currentPosition)) {
            return rand(15, 45);
        }
        
        // Extraer número de la posición (ej: "T1" -> 1, "5" -> 5)
        $pos = intval(preg_replace('/[^0-9]/', '', $currentPosition));
        
        if ($pos <= 0) {
            return rand(15, 45);
        }
        
        // Simular variación del ranking anterior (+/- 10 posiciones)
        $variation = rand(-10, 10);
        $previousRanking = $pos + $variation;
        
        // Asegurar que esté en rango razonable
        return max(1, min(50, $previousRanking));
    }
    
    /**
     * Genera rondas de ejemplo para miembros sin datos de torneo
     */
    private function generateSampleRounds() {
        return [
            rand(68, 78),
            rand(69, 79),
            rand(67, 77),
            rand(70, 80)
        ];
    }
    
    /**
     * Datos de ejemplo para testing y fallback
     */
    private function getSamplePlayersData() {
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
                'entry_number' => '83'
            ],
            [
                'id' => 2,
                'firstName' => 'William',
                'lastName' => 'Applyby',
                'affiliation' => 'Rancho Santa Fe Golf Club',
                'photo' => '',
                'highlights' => 'Mejor puntuación en par 3. Jugador consistente con gran short game.',
                'previousRanking' => 23,
                'rounds' => [72, 68, 74, 70]
            ],
            [
                'id' => 3,
                'firstName' => 'Jake',
                'lastName' => 'Byrum',
                'affiliation' => 'Phoenix County Club',
                'photo' => '',
                'highlights' => 'Especialista en torneos de verano. Excelente bajo presión.',
                'previousRanking' => 8,
                'rounds' => [70, 72, 68, 71]
            ]
        ];
    }
    
    /**
     * Obtiene información de torneos (para compatibilidad)
     */
    public function getTournaments() {
        return ['tournaments' => []];
    }
    
    /**
     * Obtiene puntuaciones (para compatibilidad)
     */
    public function getScores($tournamentId, $round = null) {
        return ['scores' => []];
    }
    
    /**
     * Adapta y normaliza la lógica de mapeo de datos de jugadores
     */
    private function adaptMappingLogic($data) {
        // Ejemplo de adaptación: Asegurar que todos los nombres de los jugadores estén recortados y capitalizados
        foreach ($data as &$player) {
            if (isset($player['firstName'])) {
                $player['firstName'] = ucfirst(trim($player['firstName']));
            }
            if (isset($player['lastName'])) {
                $player['lastName'] = ucfirst(trim($player['lastName']));
            }

            // Adaptación adicional: Validar y normalizar direcciones de correo electrónico
            if (isset($player['email']) && !filter_var($player['email'], FILTER_VALIDATE_EMAIL)) {
                $player['email'] = '';
            }

            // Asegurar que las URL de las fotos sean válidas o proporcionar un marcador de posición predeterminado
            if (empty($player['photo']) || !filter_var($player['photo'], FILTER_VALIDATE_URL)) {
                $player['photo'] = $this->generatePlaceholderPhoto($player['firstName'], $player['lastName']);
            }
        }

        return $data;
    }
}

// Función principal para manejar la lógica de la API
function main_logic() {
    try {
        $api = new GolfGeniusAPI();
        $endpoint = $_GET['endpoint'] ?? '';
        $response = ['error' => 'Endpoint no válido'];
        
        switch ($endpoint) {
            case 'players':
                $tournamentId = $_GET['tournament_id'] ?? null;
                $players = $api->getPlayers($tournamentId);
                $response = [
                    'success' => true,
                    'players' => $players,
                    'count' => count($players)
                ];
                break;
                
            case 'master_roster':
                $players = $api->getMasterRoster();
                $response = [
                    'success' => true,
                    'players' => $players,
                    'count' => count($players)
                ];
                break;
                
            case 'tournament':
                $eventId = $_GET['event_id'] ?? '10733818833262361649';
                $roundId = $_GET['round_id'] ?? '10733997704590933397';
                $tournamentId = $_GET['tournament_id'] ?? '11025765214984354975';
                
                $tournamentData = $api->getTournamentData($eventId, $roundId, $tournamentId);
                $response = [
                    'success' => true,
                    'tournament_data' => $tournamentData
                ];
                break;
                
            case 'tournaments':
                $tournaments = $api->getTournaments();
                $response = [
                    'success' => true,
                    'tournaments' => $tournaments
                ];
                break;
                
            case 'scores':
                $tournamentId = $_GET['tournament_id'] ?? null;
                $round = $_GET['round'] ?? null;
                
                if (!$tournamentId) {
                    $response = ['error' => 'tournament_id es requerido'];
                    break;
                }
                
                $scores = $api->getScores($tournamentId, $round);
                $response = [
                    'success' => true,
                    'scores' => $scores
                ];
                break;
                
            default:
                if (!isset($_GET['action'])) {
                    $response = [
                        'error' => 'Endpoint no válido. Si intenta obtener opciones, use el parámetro ?action=getTableOptions',
                        'available_endpoints' => ['players', 'master_roster', 'tournament', 'tournaments', 'scores']
                    ];
                }
                break;
        }
        
    } catch (Exception $e) {
        $response = [
            'error' => 'Error del servidor: ' . $e->getMessage(),
            'success' => false
        ];
        http_response_code(500);
    }
    
    // Devolver respuesta JSON para endpoints principales (solo si no es una acción)
    if (!isset($_GET['action'])) {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

// Manejo de acciones basadas en parámetros
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $api = new GolfGeniusAPI();

    switch ($action) {
        case 'getTableOptions':
            header('Content-Type: application/json; charset=utf-8');
            $options = $api->getTableOptions();
            echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit();

        default:
            http_response_code(400);
            echo json_encode([
                'error' => 'Acción no válida',
                'available_actions' => ['getTableOptions']
            ]);
            exit();
    }
}

// Ejecutar la lógica principal solo si no hay 'action'
main_logic();

?>
