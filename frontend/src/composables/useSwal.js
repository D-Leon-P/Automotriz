import Swal from 'sweetalert2';

export const useSwal = () => {
  const customConfirm = async (title, text, confirmText = 'Aceptar', cancelText = 'Cancelar') => {
    return Swal.fire({
      title,
      text,
      icon: 'question',
      background: '#0f172a', // Slate 900
      color: '#cbd5e1', // Slate 300
      confirmButtonColor: '#d97706', // Amber 600
      cancelButtonColor: '#334155', // Slate 700
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      buttonsStyling: true,
      customClass: {
        popup: 'border border-white/10 rounded-2xl font-sans shadow-2xl',
        title: 'text-white font-bold text-lg',
        htmlContainer: 'text-slate-400 text-sm',
        confirmButton: 'px-4 py-2 text-sm font-bold rounded-xl shadow-lg',
        cancelButton: 'px-4 py-2 text-sm font-semibold rounded-xl'
      }
    });
  };

  const confirmDelete = async (title = '¿Estás seguro?', text = 'Esta acción no se puede deshacer.') => {
    return Swal.fire({
      title,
      text,
      icon: 'warning',
      background: '#0f172a', // Slate 900
      color: '#cbd5e1', // Slate 300
      confirmButtonColor: '#ef4444', // Red 500
      cancelButtonColor: '#334155', // Slate 700
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      buttonsStyling: true,
      customClass: {
        popup: 'border border-white/10 rounded-2xl font-sans shadow-2xl',
        title: 'text-white font-bold text-lg',
        htmlContainer: 'text-slate-400 text-sm',
        confirmButton: 'px-4 py-2 text-sm font-bold rounded-xl shadow-lg',
        cancelButton: 'px-4 py-2 text-sm font-semibold rounded-xl'
      }
    });
  };

  return {
    confirm: customConfirm,
    confirmDelete
  };
};
