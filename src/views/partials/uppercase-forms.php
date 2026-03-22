<?php
/**
 * Partial: convierte a mayúsculas todo lo que se escribe en inputs y textareas.
 * Incluir una vez por página, por ejemplo antes de </body>.
 */
?>
<style>
/* Mayúsculas en formularios */
input.form-control:not([type="password"]):not([type="submit"]):not([type="button"]):not([type="hidden"]):not([type="number"]):not([type="date"]),
textarea.form-control,
input[type="text"],
input[type="email"],
input[type="search"],
textarea {
    text-transform: uppercase;
}
</style>
<script>
(function() {
    function initUppercaseForms() {
        var selector = 'input:not([type="password"]):not([type="submit"]):not([type="button"]):not([type="hidden"]):not([type="number"]):not([type="date"]):not([type="checkbox"]):not([type="radio"]), textarea';
        document.querySelectorAll(selector).forEach(function(el) {
            if (el._uppercaseBound) return;
            el._uppercaseBound = true;
            el.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
            el.addEventListener('blur', function() { this.value = this.value.toUpperCase(); });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUppercaseForms);
    } else {
        initUppercaseForms();
    }
})();
</script>
