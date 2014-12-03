var config = require("config");
var x = require('casper').selectXPath;
var utils = require('utils');

var casper = require('casper').create({
  clientScripts: [],
  logLevel: "error",           //Log all messages for debugging
  pageSettings: {
    loadImages: true,
    loadPlugins: false,
    webSecurityEnabled: false, //Download with CORS
    sslProtocol: "tlsv1"       //Use PhantomJS 1.9.7 instead of 1.9.7 and use this line, because of issue #1068
  },
  verbose: true                //Print logs on console in realtime
});

casper.start('https://fhsys.fh-salzburg.ac.at', function () {
  this.echo('Open and login to FHSYS', "INFO");

  this.fill(x('/html/body/div/div[2]/form'), {
    "j_username": config.name,
    "j_password": config.password
  }, true);
});

casper.waitForSelector({type: 'xpath', path: '//*[@id="mmlink0"]'}, function () {
  this.echo('Browse to search', "INFO");
  this.thenOpen('https://fhsys.fh-salzburg.ac.at/controller/priv/personensuche.php');
});

casper.then(function () {
  this.echo('Fill search form', "INFO");
  this.fillXPath(x("/html/body/table[2]/tbody/tr/td/form"), {
    "//table/tbody/tr[2]/td/table/tbody/tr[2]/td[3]/input": "Bergmüller",
  }, false);
  this.evaluate(function () {
    personenSuchen();
  });
});

casper.waitWhileVisible("#seiteGeneration", function () {
  //...
}, null, 4 * 60 * 1000);

casper.then(function (allLinkIds) {
  this.echo('Save overview to DB', "INFO");

  var idx = 0;
  var data = this.evaluate(function () {
    var form = document.getElementsByName('form1') [0];
    var table = form.getElementsByTagName('tbody') [0];
    var lines = table.querySelectorAll('tr.hell, tr.dunkel');
    var data = new Array();

    for (var i = 0; i < lines.length; i++) {
      var column = lines[i].children;
      var fhsId = column[6].innerHTML.split('\'') [1];

      data.push({
        'lastname': column[0].innerHTML,
        'prename': column[1].innerHTML,
        'department': column[2].innerHTML,
        'type': column[3].innerHTML,
        'id': fhsId
      });
    }

    //__utils__.sendAJAX("http://localhost/FH-Search/backend/interface/create.php", "POST", JSON.stringify({'data': data}));
    return data;
  });

  casper.repeat(data.length, function () {
    this.echo("OPENING " + (idx + 1) + ". LINK", "INFO");
    var id = data[idx].id;
    idx++;

    casper.then(function () {
      this.echo('Open one link', "INFO");

      this.evaluate(function (id) {
        personAuswaehlen(id);
      }, id);
    });

    casper.then(function () {
      this.echo('Save details to DB and picture in directory', "INFO");

      this.evaluate(function () {
        var field = document.querySelectorAll('.formborder') [0];
        var information = field.querySelectorAll('td[align]') [0];
        var email = information.childNodes[4].textContent.trim();
        var details = {
          "email": email
        };

        //TODO: Check input
        __utils__.sendAJAX("http://localhost/FB2.0/backend/interface/update.php", "POST", JSON.stringify({'details': details}));
      });

      var imageSrc = this.evaluate(function () {
        var image = document.querySelector("form table.formborder tbody tr td table tbody tr td img");
        return image.src;
      });

      this.capture("screenshot_" + data[idx-1].id + ".png");
      this.download(imageSrc, "pictures/" + data[idx-1].id + ".png");
      this.back();
    });
  });
});

casper.run(function () {
  this.echo('Finished scraping through fhsys');
  this.exit();
});

casper.on('http.status.500', function (resource) {
  this.echo('woops, 500 error: ' + resource.url);
});

casper.on('error', function (msg, backtrace) {
  this.echo("=========================");
  this.echo("ERROR:");
  this.echo(msg);
  this.echo(backtrace);
  this.echo("=========================");
});

casper.on('fill', function (selector, vals, submit) {
  this.echo('FILL ' + selector + " " + vals + " " + submit);
});
