let text = document.getElementById('apiToken').innerHTML;
const copyContent = async () => {
    try {
        await navigator.clipboard.writeText(text);
        console.log('API token copied to clipboard');
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
}
