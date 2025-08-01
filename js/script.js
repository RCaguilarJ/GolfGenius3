// Configuración global
const API_CONFIG = {
    baseUrl: 'php/api_proxy.php',
    endpoints: {
        players: 'players',
        masterRoster: 'master_roster',
        tournament: 'tournament',
        tournaments: 'tournaments',
        scores: 'scores'
    }
};

// Estado de la aplicación
const appState = {
    players: [],
    selectedColumns: ['photo', 'firstName', 'lastName', 'affiliation'],
    selectedRound: 1,
    isLoading: false
};

// Mapeo de columnas
const COLUMN_CONFIG = {
    photo: {
        header: 'Foto',
        width: '80px',
        render: (player) => {
            if (player.photo && player.photo !== '' && player.photo !== null) {
                return `<img src="${player.photo}" alt="${player.firstName} ${player.lastName}" class="player-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="player-photo placeholder" style="display:none;">${(player.firstName?.charAt(0) || '')}${(player.lastName?.charAt(0) || '')}</div>`;
            } else {
                return `<div class="player-photo placeholder">${(player.firstName?.charAt(0) || '')}${(player.lastName?.charAt(0) || '')}</div>`;
            }
        }
    },
    firstName: {
        header: 'Nombre',
        width: 'auto',
        render: (player) => player.firstName || '-'
    },
    lastName: {
        header: 'Apellido',
        width: 'auto',
        render: (player) => player.lastName || '-'
    },
    affiliation: {
        header: 'Afiliación al Club',
        width: 'auto',
        render: (player) => `<span class="affiliation">${player.affiliation || 'Sin afiliación'}</span>`
    },
    highlights: {
        header: 'Resúmenes',
        width: '200px',
        render: (player) => `<div class="highlights">${player.highlights || 'Sin información disponible'}</div>`
    },
    previousRanking: {
        header: 'Clasificación Año Anterior',
        width: '120px',
        render: (player) => {
            if (player.previousRanking) {
                return `<span class="ranking">${player.previousRanking}</span>`;
            }
            return '-';
        }
    },
    roundScore: {
        header: 'Puntuación Ronda',
        width: '120px',
        render: (player) => {
            const score = player.rounds && player.rounds[appState.selectedRound - 1];
            if (score) {
                return `<span class="score">${score}</span>`;
            }
            return '-';
        }
    },
    scorePar: {
        header: 'Score vs Par',
        width: '120px',
        render: (player) => {
            const par = 72; // Par estándar
            const score = player.rounds && player.rounds[appState.selectedRound - 1];
            if (score) {
                const diff = score - par;
                let cssClass = 'par';
                let displayScore = 'E';
                
                if (diff < 0) {
                    cssClass = 'under-par';
                    displayScore = diff.toString();
                } else if (diff > 0) {
                    cssClass = 'over-par';
                    displayScore = '+' + diff.toString();
                }
                
                return `<span class="score ${cssClass}">${displayScore}</span>`;
            }
            return '-';
        }
    }
};

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    loadInitialData();
}

function setupEventListeners() {
    // Dropdown toggle
    const insertBtn = document.getElementById('insertBtn');
    const dropdownContent = document.getElementById('dropdownContent');
    
    insertBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownContent.classList.toggle('show');
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function() {
        dropdownContent.classList.remove('show');
    });

    // Prevenir que se cierre al hacer click dentro del dropdown
    dropdownContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Checkboxes de columnas
    const checkboxes = dropdownContent.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleColumnToggle);
    });

    // Selector de ronda
    const roundSelect = document.getElementById('roundSelect');
    roundSelect.addEventListener('change', function() {
        appState.selectedRound = parseInt(this.value);
        if (appState.selectedColumns.includes('roundScore') || appState.selectedColumns.includes('scorePar')) {
            renderTable();
        }
    });

    // Botón actualizar
    const refreshBtn = document.getElementById('refreshBtn');
    refreshBtn.addEventListener('click', loadInitialData);
}

function handleColumnToggle(e) {
    const column = e.target.value;
    const isChecked = e.target.checked;
    
    if (isChecked) {
        if (!appState.selectedColumns.includes(column)) {
            appState.selectedColumns.push(column);
        }
    } else {
        appState.selectedColumns = appState.selectedColumns.filter(col => col !== column);
    }
    
    // Mostrar/ocultar selector de ronda si es necesario
    const roundSelector = document.getElementById('roundSelector');
    const needsRoundSelector = appState.selectedColumns.includes('roundScore') || 
                              appState.selectedColumns.includes('scorePar');
    
    roundSelector.style.display = needsRoundSelector ? 'flex' : 'none';
    
    renderTable();
}

async function loadInitialData() {
    try {
        setLoading(true);
        hideError();
        
        const data = await fetchGolfGeniusData();
        appState.players = data;
        
        renderTable();
    } catch (error) {
        console.error('Error loading data:', error);
        showError();
    } finally {
        setLoading(false);
    }
}

async function fetchGolfGeniusData() {
    try {
        const response = await fetch(API_CONFIG.baseUrl + '?endpoint=players');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Si la API devuelve un error
        if (data.error) {
            throw new Error(data.error);
        }
        
        return data.players || data || getSampleData();
    } catch (error) {
        console.warn('API not available, using sample data:', error.message);
        return getSampleData();
    }
}

function getSampleData() {
    return [
        {
            id: 1,
            firstName: 'Jason',
            lastName: 'Anthony',
            affiliation: 'Olympic Club',
            photo: 'https://via.placeholder.com/50x50/4CAF50/white?text=JA',
            highlights: 'Ganador del torneo regional 2024',
            previousRanking: 15,
            rounds: [68, 71, 69, 72]
        },
        {
            id: 2,
            firstName: 'William',
            lastName: 'Applyby',
            affiliation: 'Rancho Santa Fe Golf Club',
            photo: '',
            highlights: 'Mejor puntuación en par 3',
            previousRanking: 23,
            rounds: [72, 68, 74, 70]
        },
        {
            id: 3,
            firstName: 'Jake',
            lastName: 'Byrum',
            affiliation: 'Phoenix County Club',
            photo: '',
            highlights: 'Especialista en torneos de verano',
            previousRanking: 8,
            rounds: [70, 72, 68, 71]
        },
        {
            id: 4,
            firstName: 'Michael',
            lastName: 'Johnson',
            affiliation: 'Beverly Hills Country Club',
            photo: 'https://via.placeholder.com/50x50/2196F3/white?text=MJ',
            highlights: 'Campeón estatal junior',
            previousRanking: 12,
            rounds: [69, 73, 67, 74]
        },
        {
            id: 5,
            firstName: 'Sarah',
            lastName: 'Williams',
            affiliation: 'Pebble Beach Golf Links',
            photo: 'https://via.placeholder.com/50x50/FF9800/white?text=SW',
            highlights: 'Récord femenino del club',
            previousRanking: 5,
            rounds: [71, 69, 72, 68]
        }
    ];
}

function renderTable() {
    const tableHead = document.getElementById('tableHead');
    const tableBody = document.getElementById('tableBody');
    
    // Limpiar tabla
    tableHead.innerHTML = '';
    tableBody.innerHTML = '';
    
    if (appState.selectedColumns.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 40px;">Selecciona al menos una columna para mostrar</td></tr>';
        return;
    }
    
    // Crear headers
    const headerRow = document.createElement('tr');
    appState.selectedColumns.forEach(columnKey => {
        const th = document.createElement('th');
        const config = COLUMN_CONFIG[columnKey];
        th.textContent = config.header;
        if (config.width) {
            th.style.width = config.width;
        }
        headerRow.appendChild(th);
    });
    tableHead.appendChild(headerRow);
    
    // Crear filas de datos
    appState.players.forEach(player => {
        const row = document.createElement('tr');
        
        appState.selectedColumns.forEach(columnKey => {
            const td = document.createElement('td');
            const config = COLUMN_CONFIG[columnKey];
            td.innerHTML = config.render(player);
            row.appendChild(td);
        });
        
        tableBody.appendChild(row);
    });
}

function setLoading(isLoading) {
    const loadingElement = document.getElementById('loading');
    const tableContainer = document.querySelector('.table-container');
    
    appState.isLoading = isLoading;
    
    if (isLoading) {
        loadingElement.style.display = 'block';
        tableContainer.style.display = 'none';
    } else {
        loadingElement.style.display = 'none';
        tableContainer.style.display = 'block';
    }
}

function showError() {
    const errorElement = document.getElementById('error');
    errorElement.style.display = 'block';
}

function hideError() {
    const errorElement = document.getElementById('error');
    errorElement.style.display = 'none';
}

// Utilidades para debugging
window.golfApp = {
    state: appState,
    config: COLUMN_CONFIG,
    refresh: loadInitialData,
    addColumn: (column) => {
        if (!appState.selectedColumns.includes(column)) {
            appState.selectedColumns.push(column);
            renderTable();
        }
    },
    removeColumn: (column) => {
        appState.selectedColumns = appState.selectedColumns.filter(col => col !== column);
        renderTable();
    }
};
