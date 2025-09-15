/**
 * Golf Genius Elementor Plugin Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initializeGolfGeniusTables();
    });
    
    /**
     * Initialize all Golf Genius tables on the page
     */
    function initializeGolfGeniusTables() {
        $('.golf-genius-container').each(function() {
            var container = $(this);
            var tableId = container.attr('id');
            
            if (tableId) {
                setupTableEvents(container, tableId);
            }
        });
    }
    
    /**
     * Setup events for a specific table
     */
    function setupTableEvents(container, tableId) {
        // Column selector dropdown
        container.find('.golf-genius-columns-btn').on('click', function(e) {
            e.stopPropagation();
            container.find('.golf-genius-columns-dropdown').toggle();
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function() {
            container.find('.golf-genius-columns-dropdown').hide();
        });
        
        // Prevent dropdown from closing when clicking inside
        container.find('.golf-genius-columns-dropdown').on('click', function(e) {
            e.stopPropagation();
        });
        
        // Refresh button
        container.find('.golf-genius-refresh-btn').on('click', function() {
            loadTableData(container, tableId);
        });
        
        // Initial load
        loadTableData(container, tableId);
    }
    
    /**
     * Load table data via AJAX
     */
    function loadTableData(container, tableId) {
        showLoading(container);
        hideError(container);
        
        $.ajax({
            url: golf_genius_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'golf_genius_get_players',
                nonce: golf_genius_ajax.nonce
            },
            success: function(response) {
                hideLoading(container);
                
                if (response.success && response.data) {
                    renderTableData(container, response.data);
                } else {
                    showError(container);
                }
            },
            error: function() {
                hideLoading(container);
                showError(container);
            }
        });
    }
    
    /**
     * Render table data
     */
    function renderTableData(container, players) {
        var table = container.find('.golf-genius-table');
        var tbody = table.find('tbody');
        var columns = getSelectedColumns(container);
        
        tbody.empty();
        
        if (!players || players.length === 0) {
            tbody.append('<tr><td colspan="' + columns.length + '" style="text-align: center; padding: 20px;">No se encontraron datos.</td></tr>');
            return;
        }
        
        players.forEach(function(player) {
            var row = '<tr>';
            
            columns.forEach(function(column) {
                var cellContent = getCellContent(player, column);
                row += '<td data-column="' + column + '">' + cellContent + '</td>';
            });
            
            row += '</tr>';
            tbody.append(row);
        });
    }
    
    /**
     * Get selected columns for a table
     */
    function getSelectedColumns(container) {
        var columns = [];
        container.find('.golf-genius-table th').each(function() {
            var column = $(this).data('column');
            if (column) {
                columns.push(column);
            }
        });
        return columns;
    }
    
    /**
     * Get cell content based on column type
     */
    function getCellContent(player, column) {
        switch(column) {
            case 'photo':
                if (player.photo && player.photo.startsWith('http')) {
                    var initials = (player.firstName ? player.firstName.charAt(0) : '') + 
                                 (player.lastName ? player.lastName.charAt(0) : '');
                    return '<img src="' + player.photo + '" alt="' + (player.firstName || '') + ' ' + 
                           (player.lastName || '') + '" class="golf-genius-photo" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="golf-genius-avatar" style="display: none;">' + initials + '</div>';
                } else {
                    var initials = (player.firstName ? player.firstName.charAt(0) : '') + 
                                 (player.lastName ? player.lastName.charAt(0) : '');
                    return '<div class="golf-genius-avatar">' + initials + '</div>';
                }
            case 'firstName':
                return player.firstName || '';
            case 'lastName':
                return player.lastName || '';
            case 'affiliation':
                return '<span class="golf-genius-affiliation">' + (player.affiliation || '') + '</span>';
            case 'position':
                return '<span class="golf-genius-position">' + (player.position || '') + '</span>';
            case 'score':
                var scoreClass = '';
                if (player.score) {
                    if (player.score.indexOf('-') === 0) scoreClass = 'under-par';
                    else if (player.score === 'E' || player.score === '0') scoreClass = 'par';
                    else scoreClass = 'over-par';
                }
                return '<span class="golf-genius-score ' + scoreClass + '">' + (player.score || '') + '</span>';
            case 'rounds':
                if (player.rounds && Array.isArray(player.rounds)) {
                    return player.rounds.join(', ');
                }
                return '';
            case 'highlights':
                return '<span class="golf-genius-highlights">' + (player.highlights || '') + '</span>';
            case 'previousRanking':
                return '<span class="golf-genius-ranking">' + (player.previousRanking ? player.previousRanking : '') + '</span>';
            case 'email':
                return player.email ? '<a href="mailto:' + player.email + '">' + player.email + '</a>' : '';
            case 'handicap':
                return player.handicap || '';
            case 'field':
                return player.field || '';
            case 'entry_number':
                return player.entry_number || '';
            case 'phone':
                return player.phone || '';
            case 'city':
                return player.city || '';
            case 'state':
                return player.state || '';
            default:
                return player[column] || '';
        }
    }
    
    /**
     * Show loading state
     */
    function showLoading(container) {
        container.find('.golf-genius-loading').show();
        container.find('.golf-genius-table').hide();
    }
    
    /**
     * Hide loading state
     */
    function hideLoading(container) {
        container.find('.golf-genius-loading').hide();
        container.find('.golf-genius-table').show();
    }
    
    /**
     * Show error state
     */
    function showError(container) {
        container.find('.golf-genius-error').show();
        container.find('.golf-genius-table').hide();
    }
    
    /**
     * Hide error state
     */
    function hideError(container) {
        container.find('.golf-genius-error').hide();
    }
    
})(jQuery);