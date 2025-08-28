<?php
// Estilos del Encabezado
require_once "../html/encabezado.php";
?>

    <main class="p-4 flex-grow-1" id="contenido">
  <h2>Dashboard</h2>
  <div class="row">
    <div class="col-md-6 mb-4">
      <canvas id="ventasChart" height="200"></canvas>
    </div>
    <div class="col-md-6 mb-4">
      <canvas id="stockChart" height="200"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Calendario</h5>
      <input type="date" class="form-control" />
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  const ctx1 = document.getElementById('ventasChart');
  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
      datasets: [{ label: 'Ventas', data: [120, 190, 300, 500, 400], borderColor: '#007bff', fill: false }]
    }
  });
  const ctx2 = document.getElementById('stockChart');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: ['Fresa', 'Chocolate', 'Vainilla'],
      datasets: [{ label: 'Stock', data: [30, 50, 20], backgroundColor: ['#dc3545', '#6f42c1', '#20c997'] }]
    }
  });
  </script>

    </main>
  </div>
  <script>
  document.getElementById('searchGlobal').addEventListener('input', function () {{
    const text = this.value.toLowerCase();
    document.querySelectorAll('#contenido *').forEach(el => {{
      if (el.textContent.toLowerCase().includes(text)) {{
        el.style.display = '';
      }} else if (el.tagName !== 'SCRIPT' && el.tagName !== 'STYLE') {{
        el.style.display = 'none';
      }}
    }});
  }});
  </script>
  <footer class="bg-primary text-white text-center p-3 vh-20">
    <p class="mb-0">&copy; 2025 Pabell - Software y Tecnología | Desarrollado por Edward Herrera y Alejandra Palacios - ADSO 2377388 - SENA</p>
    </footer>
  </body>
  </html>