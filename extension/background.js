async function sendLink(message) {
    const url = '<your_custom_url_here';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + 'test'
            },
            body: JSON.stringify({
                link: message
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        return result;

    } catch (error) {
        // Re-throw the error to be caught by the caller
        throw error;
    }
}

chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
    if (request.action === "urlSupplied") {
        (async () => {
            try {
                const result = await sendLink(request.href);
                sendResponse({ success: true, data: result });
            } catch (error) {
                sendResponse({ success: false, error: error.message });
            }
        })();

        return true; // Keep message channel open for async response
    }
});