document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form.delete-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: '¿Confirmar eliminación?',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      } else {
        if (confirm('¿Eliminar?')) form.submit();
      }
    });
  });
});