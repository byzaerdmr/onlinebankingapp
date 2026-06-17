function formatIBAN(input) {
    // sadece rakam bırak
    input.value = input.value.replace(/[^0-9]/g, '');

    // max 24 karakter
    if (input.value.length > 24) {
        input.value = input.value.slice(0, 24);
    }

    // gruplama (4'lü)
    let formatted = input.value.match(/.{1,4}/g);
    if (formatted) {
        input.value = formatted.join(' ');
    }
}