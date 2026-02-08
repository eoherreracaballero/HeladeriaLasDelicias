<style>
    /* Contenedor para dar redondeado a la tabla */
    .table-responsive {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        background-color: white;
        padding: 0;
        margin: 20px auto;
        width: 98%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 !important; /* Reseteamos margen para el contenedor */
    }

    /* TITULOS DE COLUMNAS - AJUSTE DE VISIBILIDAD */
    th {
        background-color: #1a237e !important; /* Azul Profundo Institucional */
        color: #ffffff !important;           /* Texto Blanco Puro */
        padding: 15px 10px !important;
        text-align: center;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: 600;
        border: none !important;
        letter-spacing: 0.5px;
    }

    /* Filas de la tabla */
    td {
        padding: 12px 10px;
        text-align: center;
        border-bottom: 1px solid #edf2f7;
        color: #4a5568;
        font-size: 0.9rem;
    }

    /* Efecto Hover para filas */
    tr:hover td {
        background-color: #f8faff !important;
        color: #1a237e;
        transition: 0.2s;
    }

    /* Alternancia de colores (Zebra) */
    tr:nth-child(even) {
        background-color: #fcfcfc;
    }
</style>