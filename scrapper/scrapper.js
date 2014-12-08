/**
 * Include and define additional modules, helpers and config files
 */

var xPath = require("casper").selectXPath;
var config = require("config.json");

/**
 * Config our casper instance
 */

var casper = require("casper").create({
  logLevel: "error",
  verbose: true,              //If true => prints also remote logs on console
  pageSettings: {
    loadImages: true,
    loadPlugins: false,
    webSecurityEnabled: false, //Download and requesting with CORS
    sslProtocol: "tlsv1"       //Use PhantomJS 1.9.7 instead of 1.9.8 and use this line, because of issue #1068
  }
});

/**
 * Check for query string as command line argument
 */

var query = "";
if (casper.cli.has(0)) {
  query = casper.cli.get(0);
  query = query === "ALL" ? " " : query
} else {
  casper.echo("Search string is missing! Use this script like 'casperjs scrapper.js Musterman'", "Error");
  casper.exit();
}

/**
 * Start scrapping data from FHSYS by logging in
 */

casper.start("https://fhsys.fh-salzburg.ac.at", function () {
  this.echo("# open and login to FHSYS", "COMMENT");

  this.fill(xPath("/html/body/div/div[2]/form"), {
    "j_username": config.name,
    "j_password": config.password
  }, true);
});

/**
 * Open "Personensuche" in FHSYS
 */

casper.thenOpen("https://fhsys.fh-salzburg.ac.at/controller/priv/personensuche.php");

/**
 * Search for input string in FHSYS
 */

casper.then(function () {
  this.echo("# search for '" + query + "'", "COMMENT");
  this.fillXPath(xPath("/html/body/table[2]/tbody/tr/td/form"), {
    "//table/tbody/tr[2]/td/table/tbody/tr[2]/td[3]/input": query
  }, false);
  this.evaluate(function () {
    personenSuchen();
  });
});

/**
 * Store overview and navigate to every result to store further data
 */

casper.then(function (allLinkIds) {
  var idx = 0;
  var data = {
    length: 0
  };

  //Send overview to database
  this.then(function () {
    data = this.evaluate(function () {
      var form = document.getElementsByName("form1") [0];
      var table = form.getElementsByTagName("tbody") [0];
      var lines = table.querySelectorAll("tr.hell, tr.dunkel");
      var data = new Array();

      for (var i = 0; i < lines.length; i++) {
        var column = lines[i].children;
        var fhsId = column[6].innerHTML.split("\'") [1];

        data.push({
          "lastname": column[0].innerHTML,
          "prename": column[1].innerHTML,
          "department": column[2].innerHTML,
          "type": column[3].innerHTML,
          "id": fhsId
        });
      }

      __utils__.log("1 " + new Date().getTime(), "error");
      //__utils__.sendAJAX(config.urlInterface + "/create.php", "POST", JSON.stringify({"data": data}), false);
      __utils__.log("2 " + new Date().getTime(), "error");
      return data || {};
    });
  });

  this.thenOpen(config.urlInterface + "/create.php", {
    method: 'POST',
    data: data
  });

  this.echo("3 " + new Date().getTime())

  this.then(function () {
    this.echo("4 " + new Date().getTime())
    if (data.length > 0) {
      this.echo("# save overview of " + data.length + " entries to database", "COMMENT");
    }
    this.echo("5 " + new Date().getTime())
  })

  this.echo("6 " + new Date().getTime())
  //Click on every link to open the detailed data of a person

  this.then(function () {
    this.repeat(data.length, function () {
      var id = data[idx].id;
      idx++;

      this.echo("# open details link " + idx + "/" + data.length + ": " + id, "COMMENT");

      this.then(function () {
        this.evaluate(function (id) {
          personAuswaehlen(id);
        }, id);
      });

      //Send details of a person to database
      this.then(function () {
        this.echo("# save details to database and picture in directory", "COMMENT");
        var id = data[idx - 1].id;

        var imageSrc = this.evaluate(function (id) {
          var field = document.querySelectorAll(".formborder") [0];
          var information = field.querySelectorAll("td[align]") [0];
          var email = information.childNodes[4].textContent.trim();

          var image = document.querySelector("form table.formborder tbody tr td table tbody tr td img");

          var details = {
            "id": id,
            "url": image.src,
            "email": email
          };

          //__utils__.sendAJAX(config.urlInterface + "/update.php", "POST", JSON.stringify({"details": details}));
          return image.src;
        }, id);

        //this.capture("screenshot_" + id + ".png");
        this.download(imageSrc, "pictures/" + id + ".png");

        //Go back to the person search with only a quick result for the needed function 'personenSuchen'
        //This is a workaround for not loading the whole overview again and again
        casper.thenOpen("https://fhsys.fh-salzburg.ac.at/controller/priv/personensuche.php");
        casper.then(function () {
          var query = "Aalai"; //Random only once time existing lastname
          this.fillXPath(xPath("/html/body/table[2]/tbody/tr/td/form"), {
            "//table/tbody/tr[2]/td/table/tbody/tr[2]/td[3]/input": "Aalai"
          }, false);
          this.evaluate(function () {
            personenSuchen();
          });
        });
      });
    });
  });
});

/**
 * Execute scriped and printing message when finished
 */

casper.run(function () {
  this.echo("Finished scraping through fhsys", "INFO");
  this.exit();
});

/**
 * Event listeners
 */

casper.on("http.status.500", function (resource) {
  this.echo(resource);
});

casper.on("http.status.404", function (resource) {
  this.echo(resource);
});

/**
 * Often used functions:
 *
 * require("utils").dump({"json": "object", "readable": true});
 * require("utils").serialize();
 */