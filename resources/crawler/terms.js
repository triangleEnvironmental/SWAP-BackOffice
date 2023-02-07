const {openBrowser, goto, $, waitFor, evaluate, closeBrowser} = require('taiko');

async function getTerms() {
  try {
    await openBrowser({
      args:['--no-sandbox', '--disable-setuid-sandbox'],
      headless: true
    });
    await goto('https://app.termly.io/document/terms-of-use-for-website/77013405-bb2d-4d93-ac95-1c592cb1b731');
    await waitFor(async () => (await $("div[data-custom-class=body] > div").exists()));
    return await evaluate($("div[data-custom-class=body]"), (element) => element.outerHTML);
  } catch (error) {
    console.error(error);
  } finally {
    await closeBrowser();
  }
};

module.exports = getTerms
