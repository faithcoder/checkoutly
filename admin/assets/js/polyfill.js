// Polyfill for jQuery.isArray
if (typeof jQuery !== 'undefined' && !jQuery.isArray) {
    jQuery.isArray = Array.isArray;
}
