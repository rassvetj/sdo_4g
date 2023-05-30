(function(root, $, undef) {

// TODO remove save from required
var providers = {}
  , candidates = {}
  , DS_TIMEOUT = 10000
  , storedData = {}
  , debouncedSave = _.debounce(saveData, 10)
  , registrationClosed
  , initializationCompleted
  , initializing
  , PStore = {}
  , initDeferred = $.Deferred()
  , initPromise = initDeferred.promise(PStore);

if (root.PStore) { return; }

function isProvider (obj) {
	var providerMethods = "init save data".split( " " );
	return _.all(providerMethods, function (method) {
		return _.isFunction(obj[method]);
	});
}
function isPromise (obj) {
	var promiseMethods = "then done fail isResolved isRejected promise".split( " " )
	  , isObject = !!obj && (typeof obj == 'object');
	while (isObject && promiseMethods.length)
		if (!obj[ promiseMethods.shift() ]) return false;
	return isObject;
}
function wrapInDeferred (fn) {
	var param = _.isFunction(fn) ? fn() : fn
	  , d = param;
	if (!isPromise(d)) {
		d = $.Deferred();
		param ? d.resolve() : d.reject();
	}
	return d.promise();
}

// provider registration
function register (name, provider, priority) {
	var candidate;

	if (registrationClosed) { return; }

	// Invalid provider name
	// or provider with *name* already registered
	// or has invalid provider signature
	if (!name || !_.isString(name) || !_.isUndefined(candidates[name]) || !isProvider(provider))
		return;

	candidate = candidates[name] = {
		fn:           provider,
		priority:     priority || 0,
		name:         name,
		data:         null,
		isAvailable:  null,
		deferred:     $.Deferred() // init deferred
	};

	wrapInDeferred(provider.isAvailable)
		.always(function () { candidate.isAvailable = this.isResolved(); })
		.done(function () {
			// ignore data which arrived after init have completed
			// just for performance
			if (initializationCompleted) { return; }
			// begin initialization just after registration
			wrapInDeferred(candidate.fn.init)
				.done(function () { candidate.data = candidate.fn.data(); })
				.then(function () {
					candidate.deferred.resolve();
				}, function () {
					candidate.deferred.reject();
				});
		});
}

function initJob (timeout) {
	var unknown
	  , deferred = $.Deferred();

	// few seconds to complete on time
	_.delay(
		function () { deferred.resolve(); },
		(_.isNumber(timeout) && timeout > 11) ? timeout : DS_TIMEOUT
	);
	// trigger check of job completeness after new answer arrival
	unknown = _.reject(candidates, function (candidate) {
		return candidate.deferred.isResolved() || candidate.deferred.isRejected();
	});
	$.when.apply($, _.pluck(unknown, 'deferred'))
		.always(function () { deferred.resolve(); });

	// double resolve will not occur, so ignore timeout
	return deferred.promise();
}

function refreshJob (timeout) {
	var deferred = $.Deferred();

	// few seconds to complete on time
	_.delay(
		function () { deferred.resolve(); },
		(_.isNumber(timeout) && timeout > 11) ? timeout : DS_TIMEOUT
	);
	_.each(candidates, function (candidate) {
		candidate.refreshDeferred = wrapInDeferred(candidate.fn.refresh);
		candidate.refreshDeferred.done(function () {
			candidate.data = candidate.fn.data();
		});
	});
	$.when.apply($, _.pluck(candidates, 'refreshDeferred'))
		.always(function () { deferred.resolve(); });

	// double resolve will not occur, so ignore timeout
	return deferred.promise();
}

// Initialization - gathering saved data from providers
function initialize (timeout) {
	if (initializing)
		return initPromise;

	initializing = true;

	// Disable registration of new providers
	registrationClosed = true;

	initJob(timeout).always(function () {
		initializationCompleted = true;
		candidates = _.select(candidates, function (candidate) {
			return candidate.isAvailable;
		}).sort(function (candidate1, candidate2) {
			return (candidate1.priority || 0) - (candidate2.priority || 0);
		});
		storedData = _.reduce(candidates, function (memo, candidate) {
			return _.extend(memo, candidate.data || {});
		}, {});
		providers = _.pluck(candidates, 'fn');
		_.extend(root.PStore, {
			get:     getVar,
			set:     setVar,
			clear:   clearVars,
			refresh: refreshVars
		});

		initDeferred.resolveWith(root.PStore, []);
	});

	return root.PStore;
}
// Set variable value
function setVar (name, value, sync) {
	if (!precond(name)) { return; }
	
	if (_.isUndefined(value)) {
		delete storedData[name];
	} else {
		storedData[name] = JSON.stringify(value);
	}
	sync ? saveData() : debouncedSave();
}
// Get variable value
// TODO optimize, JSON.parse must be called less
function getVar (name, fresh) {
	if (!precond(name) || !storedData[name]) { return; }

	return JSON.parse(storedData[name]);
}
// Save data to Data Provider, sync version
// debouncedSave - async version of this function
function saveData () {
	_.each(providers, function (provider) {
		var data = provider.data() || {}
		  , diff = Diff(data, storedData);
		if (diff.length) {
			provider.save(_.extend({}, storedData), diff);
		}
	});
}
$(root).bind('unload', saveData);
// Delete all saved data
function clearVars (sync) {
	storedData = {};
	sync ? saveData() : debouncedSave();
}
var refreshPromise;
function refreshVars (timeout) {
	var deferred;
	if (refreshPromise && !(refreshPromise.isResolved() || refreshPromise.isRejected()))
		return refreshPromise;

	deferred = $.Deferred();
	refreshPromise = deferred.promise();

	refreshJob(timeout).always(function () {
		storedData = _.reduce(candidates, function (memo, candidate) {
			return _.extend(memo, candidate.data || {});
		}, {});
		deferred.resolveWith(root.PStore, []);
	});

	return refreshPromise;
}

// difference of to hashes
// returns object with properties:
// added[], removed[], changed[], length
function Diff (oldie, newie) {
	var removed = []
	  , added   = []
	  , changed = [];

	_.each(oldie, function (item, key) {
		if (_.isString(item) && !_.isString(newie[key]))
			removed.push(key);
		if (_.isString(item) && _.isString(newie[key]) && item !== newie[key])
			changed.push(key);
	});
	_.each(newie, function (item, key) {
		if (_.isString(item) && !_.isString(oldie[key]))
			added.push(key);
	});

	return {
		added:   added,
		removed: removed,
		changed: changed,
		length:  added.length + removed.length + changed.length
	};
}
function precond (name) {
	return _.isString(name) && /^[a-z0-9\-_]+$/i.test(name || '');
}

Modernizr.localstorage && register('localstorage', (function () {
	var data
	  , storage
	  , _this;

	return _this = {
		init: function () {
			storage = root.localStorage;
			return _this.refresh();
		},
		refresh: function () {
			data = {};
			for (var i = 0, length = storage.length; i < length; ++i) {
				data[storage.key(i)] = storage.getItem(storage.key(i));
			}
			return true;
		},
		save: function (newData, diff) {
			_.each(diff.removed, function (item) {
				storage.removeItem(item);
			});
			_.each(diff.added.concat(diff.changed), function (item) {
				storage.setItem(item, newData[item]);
			});
			data = newData;
		},
		data: function () { return data; },
		isAvailable: Modernizr.localstorage
	};
})());

!Modernizr.localstorage && register('userData', (function () {
	var data
	  , storage
	  , nodename = 'userdatadriver'
	  , _this;

	function getData () {
		var attrs = storage.XMLDocument.documentElement.attributes
		  , item;
		data = {};
		for (var i = 0, length = attrs.length; i < length; ++i) {
			item = attrs.item(i);
			data[item.nodeName] = item.nodeValue;
		}
	}
	function createStorage () {
		if (storage) {
			storage.parentNode.removeChild(storage);
		}
		if ( !!( document.documentElement && document.documentElement.addBehavior ) ) {
			storage = document.createElement( nodename );
			document.documentElement.insertBefore( storage, document.documentElement.firstChild );
			storage.addBehavior( "#default#userData" );
			storage.load( nodename );
		}
	}

	return _this = {
		init: function () {
			getData();
			return true;
		},
		refresh: function () {
			createStorage();
			getData();
			return true;
		},
		data: function () { return data; },
		save: function (newData, diff) {
			_.each(diff.removed, function (item) {
				storage.removeAttribute(item);
			});
			_.each(diff.added.concat(diff.changed), function (item) {
				storage.setAttribute(item, newData[item]);
			});
			storage.save( nodename );
			data = newData;
		},
		isAvailable: function () {
			try {
				createStorage();
			} catch (error) {
				storage = null;
			}
			return !!storage;
		}
	};
})());

root.PStore = _.extend(PStore, {
	init:     initialize,
	register: register
});

})(window, jQuery);
