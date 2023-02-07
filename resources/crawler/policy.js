const {openBrowser, goto, $, waitFor, evaluate, closeBrowser} = require('taiko');

async function getPolicy() {
  try {
    await openBrowser({
      args:['--no-sandbox', '--disable-setuid-sandbox'],
      headless: true
    });
    await goto('https://app.termly.io/document/privacy-policy/db9ebdb7-1a8d-439b-b7c7-80002de0da8a');
    await waitFor(async () => (await $("div[data-custom-class=body] > div").exists()));
    return await evaluate($("div[data-custom-class=body]"), (element) => element.outerHTML);
  } catch (error) {
    console.error(error);
  } finally {
    await closeBrowser();
  }
};

module.exports = getPolicy
