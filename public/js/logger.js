(function (wnd, doc) {
"use strict";

var url
  , socket
  , config
  , user
  , oldOnerror
  , defaultData
  , messages
  , poll
  , logger
  , toStr = Object.prototype.toString;

function emit (level, data) {
	if (level == null) {
		level = 'jserror'
	}
	if (socket) {
		socket.emit(level, data);
	} else {
		errors.push({
			level: level,
			message: data
		});
	}
}
function logToServer (level, message) {
	var data = {}
	  , item;
	
	if (message == null) { message = {}; }
	for (item in defaultData) { if (defaultData.hasOwnProperty(item)) {
		data[item] = defaultData[item];
	} }
	for (item in message) { if (message.hasOwnProperty(item)) {
		data[item] = message[item];
	} }
	if (data.timeStamp == null) {
		data.timeStamp = (new Date()).getTime();
	}
	emit(level, data);
}
function socketReady (s) {
	var i
	  , length = messages.length;
	
	if (s) {
		socketReady.ready = true;
		socket = s;
		if (length > 0) {
			for (i = 0; i < length; ++i) {
				emit(messages[i].level, messages[i].message);
			}
		}
	}
}
function initSocket (url) {
	var s;
	
	s = io.connect(url);
	s.on('connect', function () {
		socketReady(s);
	});
}
function isPhpTrue (str) {
	if (typeof str == 'string') {
		return str == 'true' || str == '1';
	} else {
		return !!str;
	}
}

logger = wnd.logger = {
	log: function (level, message) {
		logToServer(level, {
			message: String(message)
		});
	},
	info: function (message) {
		logger.log('info', message);
	},
	debug: function (message) {
		logger.log('debug', message);
	},
	error: function (message) {
		logger.log('error', message);
	}
};

config = doc.documentElement.getAttribute('data-config');
try {
	config = JSON.parse(config).logger;
} catch (e) {
	config = null;
}
if (!config || !isPhpTrue(config.writelog) || !config.logserver) {
	return;
}

url = config.logserver;
// prepare url
if (url.charAt(url.length - 1) == '/') {
	url = url.substr(0, url.length - 1);
}
if (!/^(http[s]?:)?\/\//.test(url)) {
	url = '//' + url;
}
url = url.split('/');
if (url.length == 3) {
	url = url.join('/') + '/log';
} else if (url.length < 3) {
	return;
} else {
	url = url.join('/');
}

oldOnerror = wnd.onerror;
messages   = [];
// Get default data
user = doc.documentElement.getAttribute('data-user');
try {
	user = JSON.parse(user);
} catch (e) {
	user = null;
}
defaultData = {
	href: doc.location.href,
	user: user,
	env:  doc.documentElement.getAttribute('data-env'),
	ua:   navigator.userAgent
};

wnd.onerror = function (msg, url, line) {
	try {
		logToServer('jserror', {
			filename: url,
			lineno:   line,
			message:  msg
		});
		if (oldOnerror) {
			oldOnerror.apply(this, arguments);
		}
	} catch (error) {}
}

poll = setInterval(function () {
	if (wnd.io) {
		clearInterval(poll);
		poll = null;
		initSocket(url);
	}
}, 100);

// clear interval after 15s timeout
setTimeout(function () {
	if (poll != null) {
		clearInterval(poll);
	}
}, 15000);

(function () {
	var script = doc.createElement("script")
	  , file   = config.socketio || ''
	  , oldScript = doc.getElementById('socketio');
	
	if (oldScript) {
		return;
	}
	
	if (!file) {
		file = url.split('/').slice(0, 3).join('/') + '/socket.io/socket.io.js';
	}
	script.setAttribute('id', 'socketio');
	script.async = true;
	script.src   = file;
	if (doc.documentElement.firstChild) {
		doc.documentElement.insertBefore(script, doc.documentElement.firstChild);
	} else {
		doc.documentElement.appendChild(script);
	}
})();

})(this, this.document);

/*$(window).bind('error', function (event) {
	var evnt = event.originalEvent;
	var data;
	var keysOfInterest = ['filename', 'lineno', 'message', 'timeStamp'];
	
	if (evnt) {
		data = _.reduce(keysOfInterest, function (memo, prop) {
			memo[prop] = evnt[prop];
			return memo;
		}, {});
		logToServer('jserror', data);
	}
});*/
