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
  if(query === " "){
    this.echo("# search for \"" + query + "\" - will require approximately 1-2 minutes", "COMMENT");
  }else{
    this.echo("# search for \"" + query + "\"", "COMMENT");
  }

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

var overview = null;
var idx = 8679;

casper.then(function () {
  //Send overview to database
  overview = this.evaluate(function () {
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

    return data || {};
  });

  casper.then(function(){
    this.echo("# save overview of " + overview.length + " entries to database", "COMMENT");
  });

  /*casper.thenOpen(config.urlInterface + "/create.php", {
    method: "POST",
    data: {
      "data": JSON.stringify(overview)
    }
  });*/
});

/**
 * Open single link and saved details for every person in the result
 */

casper.then(function () {
  this.repeat(overview.length, function () {
    var id = overview[idx].id;
    idx++;

    //Open single link from overview
    this.then(function () {
      this.echo("# open link " + idx + "/" + overview.length + ": " + id, "COMMENT");

      this.evaluate(function (id) {
        personAuswaehlen(id);
      }, id);
    });

    /**
     * Send details of a person to database
     */
    this.then(function () {
      var id = overview[idx - 1].id;

      var details = this.evaluate(function (id) {
        var field = document.querySelectorAll(".formborder") [0];
        var information = field.querySelectorAll("td[align]") [0];
        var email = information.childNodes[4].textContent.trim();
        var image = document.querySelector("form table.formborder tbody tr td table tbody tr td img");

        var url = "";
        if(image.src !== "https://fhsys.fh-salzburg.ac.at/img/keinBild.gif"){
          url = image.src;
        }

        return {
          "id": id,
          "url": url,
          "email": email
        };
      }, id);

      /**
       * Save details to database
       */
      this.thenOpen(config.urlInterface + "/update.php", {
        method: "POST",
        data: {
          "details": JSON.stringify(details)
        }
      }, function (response) {
        this.echo("# save details of " + idx + "/" + overview.length + ": " + id, "COMMENT");
      });

      /**
       * Save picture to directory and wait for one second to not overstrain fhsys to much
       */
      this.then(function(){
        if(details.url !== ""){
          this.download(details.url, "pictures/" + id + ".png");
        }

        this.wait(1000);
      });
    });

    /**
     * Go back to the person search with only a quick result for the needed function 'personenSuchen'
     * This is a workaround for not loading the whole overview again and again
     */
    this.then(function () {
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

/**
 * Execute script and printing message when finished
 */

casper.run(function () {
  this.echo("Finished scraping through fhsys", "INFO");
  this.exit();
});

/**
 * Event listeners
 */

casper.on("http.status.500", function (resource) {
  //this.echo("HTTP STATUS CODE 500");
});

casper.on("http.status.200", function (resource) {
  //this.echo("HTTP STATUS CODE 200");
});

casper.on("http.status.304", function (resource) {
  //this.echo("HTTP STATUS CODE 304");
});

casper.on("http.status.501", function (resource) {
  //this.echo("HTTP STATUS CODE 501");
});

/**
 * Often used functions:
 *
 * require("utils").dump({"json": "object", "readable": true});
 * require("utils").serialize();
 */