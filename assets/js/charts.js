(function () {
  const salesCtx = document.getElementById('salesChart');
  const growthCtx = document.getElementById('growthChart');
  const chartData = window.adminChartData || {};
  if (!salesCtx && !growthCtx) return;

  if (salesCtx) {
    new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: chartData.revenueLabels || [],
        datasets: [{
          label: 'Revenue ($)',
          data: chartData.revenue || [],
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
        labels: chartData.userLabels || [],
        datasets: [
          { label: 'Users', data: chartData.users || [], backgroundColor: '#2563EB' }
        ]
      },
      options: { responsive: true }
    });
  }
})();