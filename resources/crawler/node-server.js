const express = require('express')
const bodyParser = require('body-parser')
const app = express()
const getTerms = require('./terms.js')
const getPolicy = require('./policy.js')

app.use(bodyParser.json({
  limit: '50mb',
}));
app.use(bodyParser.urlencoded({
  limit: '50mb',
  extended: true
}));

app.get('/terms', async function (req, res) {
  // const post = req.body.post
  res.send(await getTerms());
});

app.get('/policy', async function (req, res) {
  // const post = req.body.post
  res.send(await getPolicy());
});

app.listen(2022, function () {
  console.log('Node listening on port ' + 2022 + '!');
});
