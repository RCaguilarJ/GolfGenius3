/**
 * Golf Genius Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initializeAdminPage();
    });
    
    /**
     * Initialize admin page functionality
     */
    function initializeAdminPage() {
        // Generate shortcode button
        $('#generate-shortcode').on('click', function() {
            generateShortcode();
        });
        
        // Test API button
        $('#test-api').on('click', function() {
            testApiConnection();
        });
        
        // Column checkboxes change
        $('.columns-selector input[type="checkbox"]').on('change', function() {
            updatePreview();
        });
        
        // Form field changes
        $('#table-title, #table-class, #show-refresh').on('change', function() {
            updatePreview();
        });
        
        // Initial preview update if there are selected columns
        if ($('.columns-selector input[type="checkbox"]:checked').length > 0) {
            updatePreview();
        }
    }
    
    /**
     * Generate shortcode based on current form values
     */
    function generateShortcode() {
        var columns = [];
        $('.columns-selector input[type="checkbox"]:checked').each(function() {
            columns.push($(this).val());
        });
        
        if (columns.length === 0) {
            alert('Por favor selecciona al menos una columna.');
            return;
        }
        
        var data = {
            action: 'golf_genius_generate_shortcode',
            columns: columns,
            title: $('#table-title').val(),
            class: $('#table-class').val(),
            show_refresh: $('#show-refresh').val(),
            nonce: $('input[name="golf_genius_admin_nonce"]').val() || $('#generate-shortcode').data('nonce')
        };
        
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                displayShortcode(response.data.shortcode);
                if (response.data.preview) {
                    displayPreview(response.data.preview);
                }
            } else {
                alert('Error al generar el shortcode: ' + (response.data ? response.data.message : 'Error desconocido'));
            }
        }).fail(function() {
            alert('Error de conexión al generar el shortcode.');
        });
    }
    
    /**
     * Test API connection
     */
    function testApiConnection() {
        var button = $('#test-api');
        var originalText = button.text();
        
        button.prop('disabled', true).text('Probando...');
        
        var data = {
            action: 'golf_genius_test_api',
            api_key: $('#golf_genius_api_key').val(),
            event_id: $('#golf_genius_event_id').val(),
            round_id: $('#golf_genius_round_id').val(),
            tournament_id: $('#golf_genius_tournament_id').val(),
            nonce: $('#test-api').data('nonce') || $('input[name="golf_genius_admin_nonce"]').val()
        };
        
        $.post(ajaxurl, data, function(response) {
            button.prop('disabled', false).text(originalText);
            
            var resultHtml = '';
            if (response.success) {
                resultHtml = '<div class="notice notice-success"><p>' + response.data.message + '</p></div>';
            } else {
                resultHtml = '<div class="notice notice-error"><p>' + (response.data ? response.data.message : 'Error desconocido') + '</p></div>';
            }
            
            $('#api-test-result').html(resultHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('#api-test-result .notice').fadeOut();
            }, 5000);
            
        }).fail(function() {
            button.prop('disabled', false).text(originalText);
            $('#api-test-result').html('<div class="notice notice-error"><p>Error de conexión al probar la API.</p></div>');
        });
    }
    
    /**
     * Update preview based on current form values
     */
    function updatePreview() {
        var columns = [];
        $('.columns-selector input[type="checkbox"]:checked').each(function() {
            columns.push($(this).val());
        });
        
        if (columns.length === 0) {
            $('#table-preview').html('<p>Selecciona al menos una columna para ver la vista previa.</p>');
            return;
        }
        
        var title = $('#table-title').val();
        var showRefresh = $('#show-refresh').val() === 'true';
        
        var preview = '<div class="golf-genius-preview">';
        
        if (title) {
            preview += '<h3>' + escapeHtml(title) + '</h3>';
        }
        
        preview += '<table class="golf-genius-table">';
        preview += '<thead><tr>';
        
        columns.forEach(function(column) {
            var header = getColumnLabel(column);
            preview += '<th>' + escapeHtml(header) + '</th>';
        });
        
        preview += '</tr></thead>';
        preview += '<tbody><tr><td colspan="' + columns.length + '">Vista previa - Los datos se cargarán dinámicamente</td></tr></tbody>';
        preview += '</table>';
        
        if (showRefresh) {
            preview += '<button class="button" disabled>Actualizar Tabla</button>';
        }
        
        preview += '</div>';
        
        $('#table-preview').html(preview);
    }
    
    /**
     * Display generated shortcode
     */
    function displayShortcode(shortcode) {
        var html = '<p><strong>Tu shortcode:</strong></p>' +
                   '<div class="shortcode-display">' +
                   '<input type="text" value="' + escapeHtml(shortcode) + '" readonly onclick="this.select()">' +
                   '<button class="button" onclick="copyToClipboard(\'' + escapeHtml(shortcode) + '\')">Copiar</button>' +
                   '</div>';
        
        $('#shortcode-result').html(html);
    }
    
    /**
     * Display preview
     */
    function displayPreview(preview) {
        $('#table-preview').html(preview);
    }
    
    /**
     * Get column label
     */
    function getColumnLabel(column) {
        var labels = {
            'photo': 'Foto',
            'firstName': 'Nombre',
            'lastName': 'Apellido',
            'affiliation': 'Afiliación',
            'position': 'Posición',
            'score': 'Puntuación',
            'rounds': 'Rondas',
            'highlights': 'Destacados',
            'previousRanking': 'Ranking Anterior',
            'email': 'Email',
            'handicap': 'Handicap',
            'field': 'Campo',
            'entry_number': 'N° Entrada',
            'phone': 'Teléfono',
            'city': 'Ciudad',
            'state': 'Estado'
        };
        
        return labels[column] || column;
    }
    
    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Global function for copying to clipboard
    window.copyToClipboard = function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showCopySuccess();
            }, function() {
                fallbackCopyTextToClipboard(text);
            });
        } else {
            fallbackCopyTextToClipboard(text);
        }
    };
    
    /**
     * Fallback copy function for older browsers
     */
    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                showCopyError();
            }
        } catch (err) {
            showCopyError();
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Show copy success message
     */
    function showCopySuccess() {
        var notice = $('<div class="notice notice-success" style="position: fixed; top: 32px; right: 20px; z-index: 9999; max-width: 300px;"><p>¡Shortcode copiado al portapapeles!</p></div>');
        $('body').append(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 2000);
    }
    
    /**
     * Show copy error message
     */
    function showCopyError() {
        alert('No se pudo copiar automáticamente. Por favor, selecciona el texto y copia manualmente.');
    }
    
})(jQuery);