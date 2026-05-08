function copyToClipboard(elementId, iconBtnId) {
    const input = document.getElementById(elementId);
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    const textToCopy = input.value;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                animateCopyIcon(iconBtnId);
            }).catch(() => {
                // Fallback to execCommand if clipboard API fails
                fallbackCopy(textToCopy, iconBtnId);
            });
        } else {
            fallbackCopy(textToCopy, iconBtnId);
        }
    } catch (err) {
        fallbackCopy(textToCopy, iconBtnId);
    }
}

function fallbackCopy(text, iconBtnId) {
    document.execCommand('copy');
    animateCopyIcon(iconBtnId);
}

function animateCopyIcon(iconBtnId) {
    const iconBtn = document.getElementById(iconBtnId);
    if (!iconBtn || iconBtn.classList.contains('copied')) return;

    iconBtn.classList.add('copied');
    
    setTimeout(() => {
        iconBtn.classList.remove('copied');
    }, 2000);
}
