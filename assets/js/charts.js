(function () {
  const salesCtx = document.getElementById('salesChart');
  const growthCtx = document.getElementById('growthChart');
  if (!salesCtx && !growthCtx) return;

  if (salesCtx) {
    new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
          label: 'Revenue ($)',
          data: [12000, 19000, 15000, 22000, 28000, 35000],
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37,99,235,0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  if (growthCtx) {
    new Chart(growthCtx, {
      type: 'bar',
      data: {
        labels: ['Students', 'Teachers'],
        datasets: [
          { label: 'Jan', data: [400, 40], backgroundColor: '#2563EB' },
          { label: 'Jun', data: [1200, 120], backgroundColor: '#F59E0B' }
        ]
      },
      options: { responsive: true }
    });
  }
})();