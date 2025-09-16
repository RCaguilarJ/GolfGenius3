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

// Standard text to show when a field is missing in the data
const MISSING_DATA_TEXT = 'not found';

// Estado de la aplicación
const appState = {
    players: [],
    selectedColumns: [
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
    ],
    selectedRound: 1,
    isLoading: false
};

// Mapeo de columnas
const COLUMN_CONFIG = {
    photo: {
        header: 'Photo',
        width: '80px',
        render: (player) => {
            const initials = `${player.firstName?.charAt(0) || ''}${player.lastName?.charAt(0) || ''}`;
            const backgroundColor = generateAvatarColor(initials);
            const avatar = `<div class="player-avatar" style="background-color: ${backgroundColor};">${initials}</div>`;

            if (player.photo && player.photo.startsWith('http')) {
                return `<img src="${player.photo}" alt="${player.firstName} ${player.lastName}" class="player-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="player-avatar" style="background-color: ${backgroundColor}; display: none;">${initials}</div>`;
            } else {
                return avatar;
            }
        }
    },
    firstName: {
        header: 'First Name',
        width: 'auto',
        render: (player) => player.firstName || MISSING_DATA_TEXT
    },
    lastName: {
        header: 'Last Name',
        width: 'auto',
        render: (player) => player.lastName || MISSING_DATA_TEXT
    },
    affiliation: {
        header: 'Club Affiliation',
        width: 'auto',
        render: (player) => `<span class="affiliation">${player.affiliation || 'No affiliation'}</span>`
    },
    highlights: {
        header: 'Highlights',
        width: '200px',
        render: (player) => `<div class="highlights">${player.highlights || 'No information available'}</div>`
    },
    previousRanking: {
        header: 'Previous Year Tournament Ranking',
        width: '120px',
        render: (player) => {
            if (player.previousRanking) {
                return `<span class="ranking">${player.previousRanking}</span>`;
            }
            return MISSING_DATA_TEXT;
        }
    },
    roundScore: {
        header: 'Full Round Score',
        width: '120px',
        render: (player) => {
            const score = player.rounds && player.rounds[appState.selectedRound - 1];
            if (score) {
                return `<span class="score">${score}</span>`;
            }
            return MISSING_DATA_TEXT;
        }
    },
    scorePar: {
        header: 'Winning Score vs Par',
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
            return MISSING_DATA_TEXT;
        }
    },
    position: {
        header: 'Position',
        width: 'auto',
        render: (player) => player.position || MISSING_DATA_TEXT
    },
    score: {
        header: 'Score',
        width: 'auto',
        render: (player) => player.score || MISSING_DATA_TEXT
    },
    rounds: {
        header: 'Rounds',
        width: 'auto',
        render: (player) => player.rounds ? player.rounds.join(', ') : MISSING_DATA_TEXT
    },
    email: {
        header: 'Email',
        width: 'auto',
        render: (player) => player.email || MISSING_DATA_TEXT
    },
    handicap: {
        header: 'Handicap',
        width: 'auto',
        render: (player) => player.handicap || MISSING_DATA_TEXT
    },
    field: {
        header: 'Field',
        width: 'auto',
        render: (player) => player.field || MISSING_DATA_TEXT
    },
    entry_number: {
        header: 'Entry Number',
        width: 'auto',
        render: (player) => player.entry_number || MISSING_DATA_TEXT
    },
    phone: {
        header: 'Phone',
        width: 'auto',
        render: (player) => player.phone || MISSING_DATA_TEXT
    },
    city: {
        header: 'City',
        width: 'auto',
        render: (player) => player.city || MISSING_DATA_TEXT
    },
    state: {
        header: 'State',
        width: 'auto',
        render: (player) => player.state || MISSING_DATA_TEXT
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
        console.log('Botón Select Columns clickeado');
        dropdownContent.classList.toggle('show');
        console.log('Clase aplicada:', dropdownContent.classList.contains('show'));
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
        checkbox.addEventListener('change', function(e) {
            const column = e.target.value;
            const isChecked = e.target.checked;

            if (isChecked) {
                if (!appState.selectedColumns.includes(column)) {
                    appState.selectedColumns.push(column);
                }
            } else {
                appState.selectedColumns = appState.selectedColumns.filter(col => col !== column);
            }

            console.log('Columnas seleccionadas:', appState.selectedColumns);
            renderTable();
        });
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

async function loadInitialData() {
    try {
        setLoading(true);
        hideError();
        
        const data = await fetchGolfGeniusData();
        appState.players = data;
        
        console.log('Columnas seleccionadas:', appState.selectedColumns);
        console.log('Datos de jugadores:', appState.players);
        // Debug: Verificar datos de jugadores
        console.log('Player Data:', appState.players);
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
        const response = await fetch(`${API_CONFIG.baseUrl}?endpoint=players`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Si la API devuelve un error
        if (data.error) {
            console.error(`Error del servidor: ${data.error}`);
            console.info('Endpoints disponibles:', data.available_endpoints);
            throw new Error(data.error);
        }

        return data.players || data || getSampleData();
    } catch (error) {
        console.warn('API not available, using sample data:', error.message);
        return getSampleData();
    }
}

// Función para obtener opciones de columnas desde el backend
async function fetchTableOptions() {
    try {
        const response = await fetch(`${API_CONFIG.baseUrl}?action=getTableOptions`);
        if (!response.ok) {
            throw new Error('Error al obtener las opciones de la tabla');
        }
        const responseText = await response.text();
        console.log('Respuesta del servidor (raw):', responseText);

        // Filtrar y extraer el JSON válido
        const jsonMatch = responseText.match(/\[.*\]/s);
        if (!jsonMatch) {
            throw new Error('Formato de respuesta inválido: No se encontró un array JSON');
        }

        let options;
        try {
            options = JSON.parse(jsonMatch[0]);
        } catch (parseError) {
            throw new Error(`Error al parsear JSON: ${parseError.message}`);
        }

        // Actualizar el menú desplegable
        const dropdownContent = document.getElementById('dropdownContent');
        if (!dropdownContent) {
            console.error('Elemento dropdownContent no encontrado');
            return;
        }
        dropdownContent.innerHTML = '';
        options.forEach(option => {
            const label = document.createElement('label');
            label.innerHTML = `<input type="checkbox" value="${option}" checked> ${COLUMN_CONFIG[option]?.header || option}`;
            dropdownContent.appendChild(label);
        });

        // Actualizar columnas seleccionadas
        appState.selectedColumns = options;

        // Renderizar la tabla con las nuevas columnas
        renderTable();
    } catch (error) {
        console.error('Error:', error);
    }
}

// Llamar a la función al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    fetchTableOptions();
});

function getSampleData() {
    return [
        {
            id: 1,
            firstName: 'Jason',
            lastName: 'Anthony',
            affiliation: 'Olympic Club',
            photo: 'https://via.placeholder.com/50x50/4CAF50/white?text=JA',
            highlights: 'Regional tournament winner 2024',
            previousRanking: 15,
            rounds: [68, 71, 69, 72]
        },
        {
            id: 2,
            firstName: 'William',
            lastName: 'Applyby',
            affiliation: 'Rancho Santa Fe Golf Club',
            photo: '',
            highlights: 'Best par-3 score',
            previousRanking: 23,
            rounds: [72, 68, 74, 70]
        },
        {
            id: 3,
            firstName: 'Jake',
            lastName: 'Byrum',
            affiliation: 'Phoenix County Club',
            photo: '',
            highlights: 'Summer tournament specialist',
            previousRanking: 8,
            rounds: [70, 72, 68, 71]
        },
        {
            id: 4,
            firstName: 'Michael',
            lastName: 'Johnson',
            affiliation: 'Beverly Hills Country Club',
            photo: 'https://via.placeholder.com/50x50/2196F3/white?text=MJ',
            highlights: 'State junior champion',
            previousRanking: 12,
            rounds: [69, 73, 67, 74]
        },
        {
            id: 5,
            firstName: 'Sarah',
            lastName: 'Williams',
            affiliation: 'Pebble Beach Golf Links',
            photo: 'https://via.placeholder.com/50x50/FF9800/white?text=SW',
            highlights: 'Club women\'s record holder',
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

// Helper function to generate avatar colors
function generateAvatarColor(initials) {
    const colors = ['#FFD700', '#ADFF2F', '#FF69B4', '#87CEEB', '#FFA07A'];
    const index = (initials.charCodeAt(0) + initials.charCodeAt(1)) % colors.length;
    return colors[index];
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
