var mouse = require("mouse").create(casper);
var x = require('casper').selectXPath;
var user = require("config");

casper.test.begin('FHSYS Tests', 2, function suite(test) {
  casper.start("https://fhsys.fh-salzburg.ac.at", function () {
    test.assertExists({type: 'xpath', path: '/html/body/div/div[2]/form'}, "main form for login is found");

    this.then(function () {
      this.fill(x('/html/body/div/div[2]/form'), {
        "j_username": user.name,
        "j_password": user.password
      }, true);
    });

    this.waitForSelector({type: 'xpath', path: '//*[@id="mmlink0"]'}, function () {

      this.mouse.move("#mmlink0");
      this.mouse.click("#mmlink0");
      this.mouse.down("#mmlink0");
      casper.capture("screenshot0.png");

      /*
      casper.capture("screenshot1.png");
      this.mouse.move({type: 'xpath', path: '//*[@id="mmlink5"]'});
      casper.capture("screenshot2.png");
      test.assertExists({
        type: 'xpath',
        path: '//*[@id="mmlink8"]'
      }, "menu button search is found");*/
    });
  });

  casper.run(function () {
    test.done();
  });
});