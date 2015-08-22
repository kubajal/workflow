
;var SWPX = (typeof SWPX == 'undefined') ? {} : SWPX;
(function(spiceworks) {
  var Cmd = function(buffer) {
    if (!(this instanceof Cmd)) {
      return new Cmd(buffer);
    }

    var self = this,
        run = function() {
          while(this.buffer.length) { this.execute(this.buffer.shift());}
        },
        execute = function(c) {
          var cmd;
          if (typeof(c) == 'function') {
            cmd = c;
          } else if (typeof(c) == 'object' && c.cmd) {
            cmd = c.cmd;
          } else {
            console.log(c);
          }

          if (cmd) {
            try {
              cmd();
            } catch (e) {
              console.log("error: " + e);
              // swallow
            }
          }
        },
        push = function(e) {
          var x;
          for (x = 0; x < arguments.length; x++) {
            this.buffer.push(arguments[x]);
          }
          this.run();
        };

    self.buffer = buffer || [];
    self.run = run;
    self.execute = execute;
    self.push = push;

    return self;

  };
  spiceworks.pixel = function() {
    var that = {
      parameters : {},
      pixel : null,
      identifier : null,
      errors : [],
      useMarketo : false,
      useEloqua : false,
      isLocal : false,
      requireLocal: false,
      setPixel : function(name) {
        if (!!name) {
          if (encodeURIComponent(name) != name) {
            this.errors.push('pixel name contains invalid characters');
          } else if (name.length != 4) {
            this.errors.push('pixel name should be four characters');
          } else {
            this.pixel = name;
          }
        }
      },
      setIdentifier : function(id) {
        this.identifier = id;
      },
      setRequireLocal : function(reqLocal) {
        this.requireLocal = reqLocal;
      },
      setParameter : function(name, value) {
        if (encodeURIComponent(name) == 'id') {
          this.errors.push('cannot set a parameter with the name "id"');
        } else {
          this.parameters[name] = value;
        }
      },
      fire : function() {
        if (!this.pixel) {
          this.errors.push('pixel was not set');
          return;
        }
        if (!this.requireLocal || this.isLocal) {
          var url = this.buildURL(),
              head = this.getBody(),
              e = document.createElement('img');
          e.setAttribute('width', '1');
          e.setAttribute('height', '1');
          e.setAttribute('src', url);
          head.appendChild(e);
          this.notifyMarketo();
          this.notifyEloqua();
        }
      },
      enableMarketo : function() {
	      this.useMarketo = true;
      },
      notifyMarketo : function() {
        if(this.useMarketo) {
          munchkin.munchkinfunction('visitwebpage', {
            url: '/spiceworks_user', params: 'spiceid=nlfkibicj5w3&pixel=' + this.pixel
          });
        }
      },
      enableEloqua : function() {
        this.useEloqua = true;
      },
      notifyEloqua : function() {
        if(this.useEloqua) {
          var _elqQ = _elqQ || [];
          _elqQ.push(['elqTrackPageView', 'http://www.spiceworks.com?spiceId=nlfkibicj5w3&pixel=' + this.pixel]);
        }
      },
      buildURL : function() {
        var base = '//px.spiceworks.com/px/' + this.pixel;
        params = this.buildParameterString();

        return base + '?' + params;
      },
      generateCacheBuster : function() {
        return 'buster=' + Math.floor(Math.random() * 100000);
      },
      buildParameterString : function() {
        var params = [],
            x;

        params.push(this.generateCacheBuster());

        if (this.identifier) {
          params.push('id=' + encodeURIComponent(this.identifier));
        }

        for (x in this.parameters) {
          params.push(encodeURIComponent(x) + '=' + encodeURIComponent(this.parameters[x]));
        }

        return params.join('&');
      },
      getBody : function() {
        return ((document.getElementsByTagName('body') || [null])[0] || (document.body) || (document.getElementsByTagName('script')[0] == null ? null : document.getElementsByTagName('script')[0].parentNode));
      }
    };

    return that;
  }();
  spiceworks.cmd = new Cmd(spiceworks.cmd);
  spiceworks.cmd.run();
})(SWPX);
