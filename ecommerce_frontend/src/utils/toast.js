import { toast } from 'vue3-toastify';

export const showToast = {
    success(message) {
        toast.success(message, {
            icon: '✅',
        });
    },
    error(message) {
        toast.error(message, {
            icon: '❌',
        });
    },
    info(message) {
        toast.info(message, {
            icon: 'ℹ️',
        });
    },
    warning(message) {
        toast.warning(message, {
            icon: '⚠️',
        });
    },
};