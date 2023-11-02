const sourceCheck = document.getElementById("sourceCheck");
const generatedSource = document.getElementById("generatedSource");

sourceCheck.addEventListener("change", function () {
    if (sourceCheck.checked) {
        generatedSource.classList.remove("w-hidden");
    } else {
        generatedSource.classList.add("w-hidden");
    }
});

const codeElement = document.getElementById('code');
const copyButton = document.getElementById('copy-button');

copyButton.addEventListener('click', () => {
    const range = document.createRange();
    range.selectNode(codeElement);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);

    document.execCommand('copy');
    window.getSelection().removeAllRanges();

    copyButton.innerHTML = '<span class="fa-icon-regular"></span>\n' +
        'Copied!';

    setTimeout(() => {
        copyButton.innerHTML = '<span class="fa-icon-regular"></span>\n' +
            'Copy Code';
    }, 1500);
});
