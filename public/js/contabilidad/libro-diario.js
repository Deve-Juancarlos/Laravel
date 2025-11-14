document.addEventListener('DOMContentLoaded', function () {
  
    document.querySelectorAll('.stat-value').forEach(element => {
        const value = parseFloat(element.getAttribute('data-value')) || 0;
        const startValue = 0;
        
        if (element.textContent.startsWith('S/ ')) {
            element.textContent = 'S/ 0.00';
            animateValue(element, startValue, value, 1000); 
        } else {
            element.textContent = '0';
            animateValue(element, startValue, value, 1000); 
        }
    });

});

function exportarExcel() {
    const form = document.getElementById('filter-form');
    const params = new URLSearchParams(new FormData(form)).toString();
    const url = `/contador/libro-diario/exportar?${params}&formato=excel`;
    window.location.href = url;
}

function exportarPDF() {

    const form = document.getElementById('filter-form');
    const params = new URLSearchParams(new FormData(form)).toString();
    const url = `/contador/libro-diario/exportar?${params}&formato=pdf`;
    window.location.href = url;
}

function confirmarEliminacion(asientoId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción enviará una SOLICITUD al Administrador para eliminar el asiento. ¿Continuar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, solicitar eliminación',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if(typeof showLoading === 'function') showLoading();
                
                const form = document.getElementById(`delete-form-${asientoId}`);
                if (form) {
                    form.submit();
                }
            }
        });
    } else {

        if (confirm('¿Estás seguro de enviar la solicitud de eliminación?')) {
            if(typeof showLoading === 'function') showLoading();
            const form = document.getElementById(`delete-form-${asientoId}`);
            if (form) {
                form.submit();
            }
        }
    }
}


function animateValue(element, start, end, duration) {
    const range = end - start;
    if (range === 0) {
        let formattedValue = (end.toString().includes('.')) ? end.toFixed(2) : end.toString();
        formattedValue = formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        if (element.textContent.startsWith('S/ ')) {
            element.textContent = 'S/ ' + formattedValue;
        } else {
            element.textContent = formattedValue;
        }
        return;
    }
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        const isDecimal = current.toString().includes('.') || end.toString().includes('.');
        const isCurrency = element.textContent.startsWith('S/ ');

        let formattedValue = isDecimal ? 
            current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : 
            current.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        element.textContent = isCurrency ? 'S/ ' + formattedValue : formattedValue;
    }, 16);
}