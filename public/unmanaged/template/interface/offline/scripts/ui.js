/**
 * @author betalb
 */

var QParams = document.location.toString().toQueryParams();
var QParentParams = window.parent.document.location.toString().toQueryParams();
var Xml = window.parent.XmlRoot;
var TestNode = window.parent.TestNode;
var TestObject = null;
var Xsl = window.parent.XsltTest;

var Resizers = (function() {
	var _Resizers = {}; // РЎРїРёСЃРѕРє РІСЃРµС… С„СѓРЅРєС†РёР№, РєРѕРЅС‚СЂРѕР»РёСЂСѓСЋС‰РёС… СЂР°Р·РјРµСЂ
	var _ResizersOrder = [];
	
	return {
		resize: function(event, itemId) {
			if (itemId && _Resizers[itemId]) {
				_Resizers[itemId](event);
				return;
			}
			for (var i = 0, length = _ResizersOrder.length; i < length; ++i) {
				_Resizers[_ResizersOrder[i]](event);
			}
		},
		add: function(itemId, resizeFunction) {
			if (!$.isFunction( resizeFunction )) {
				throw "Invalid resize handler";
			}
			if (!itemId || typeof itemId != 'string') {
				throw "Invalid handler id";
			}
			if (_Resizers[itemId]) { return false; }
			_Resizers[itemId] = resizeFunction;
			_ResizersOrder.push(itemId);
			return true;
		}
	};
})();

Resizers.add('content', function(event) {
	var ac = arguments.callee;
	if (!ac.content || !ac.content.length) {
		ac.content = $('#content');
		ac.layoutBottom = $('#layout-bottom');
	}
	if (!ac.content.length) { return; }
	var ContentWidth = $(window).width();
	var ContentHeight = $(window).height()
		- ac.layoutBottom.height();
	ac.layoutBottom.css({
		width: $(window).width()
	});
	ac.content.css({
		width: ContentWidth < 400 ? 400 : ContentWidth,
		height: ContentHeight < 150 ? 150 : ContentHeight
	});
});

// РџСЂРё Р·Р°РіСЂСѓР·РєРµ С†РµРїР»СЏРµРј РЅРµРѕР±С…РѕРґРёРјС‹Рµ СЃРѕР±С‹С‚РёСЏ Рё РїРѕРєР°Р·С‹РІР°РµРј РєСѓСЂСЃ
$(function() {
	$(window).bind('resize', function(event, itemId) {
		Resizers.resize(event, itemId);
	});
});
$(window).bind('load', function(event) {
	loadingProxy();
	$(window).trigger('resize');
});

/* BEGIN: РѕР±СЂР°Р±РѕС‚РєР° С‚РµРєСЃС‚Р° */
function text_transform(text, id, title) {
	text = text.norm();
	var innerLinks = text.match(/<[Aa] class=(\"term\"|term) title=(\"alt\"|alt) href=\"about:blank#\" InnerLink=\"(EUL:)?[{}\-a-zA-Z0-9]+\">.*?<\/[Aa]>/g);
	var ilRegEx = /^<[Aa].*?InnerLink=\"(EUL:)?([{}\-a-zA-Z0-9]+)\".*?>(.*?)<\/[Aa]>$/;
	var matches,
	    element;
	if (innerLinks) {
		var length = innerLinks.length;
		for (var i = 0; i < length; ++i) {
			matches = innerLinks[i].match(ilRegEx);
			if (matches && matches.length == 4) {
				element = _Xml.$(head, matches[2]);
				if (element) {
					text = text.replace(innerLinks[i],
						'<a title="'+_Xml.title(element)+'" class="inner-link" href="inner-link:'+_Xml.name(element)+'" id="INNER-LINK-'+_Xml.id(element)+'-'+i+'-EU" onclick="processInnerLinks(this, \''+_Xml.id(element)+'\'); return false;">'+matches[3]+'</a>'
					);
				} else {
					text = text.replace(innerLinks[i], matches[3]);
				}
			}
		}
	}

	if (!id) {
		return text;
	} else {
		try {
			$('#' + id).html(text)
		} catch (error) {}
	}
}
/* END: РѕР±СЂР°Р±РѕС‚РєР° С‚РµРєСЃС‚Р° */

/* BEGIN: РѕР±СЂР°Р±РѕС‚РєР° РІРЅСѓС‚СЂРµРЅРЅРёС… СЃСЃС‹Р»РѕРє */
function processInnerLinks(link, id) {
	return false;
}
/* END: РѕР±СЂР°Р±РѕС‚РєР° РІРЅСѓС‚СЂРµРЅРЅРёС… СЃСЃС‹Р»РѕРє */
function disableButtons() {
	$('#layout-bottom .button')
		.addClass('disabled-button');
}
function enableButtons() {
	$('#layout-bottom .button')
		.removeClass('disabled-button');
}
function setButton(buttonType, handler, title) {
	if (!/next|previous|answer/.test(buttonType)) { return; }
	var button = $('#button-'+ buttonType);
	if (!handler) {
		button
			.unbind('click')
			.hide();
		return;
	}
	button
		.unbind('click')
		.click(function(event) {
			if ($(this).is('.disabled-button')) {
				return;
			}
			handler(event);
			return false;
		})
		.find('.button-text')
		.text(title)
		.end()
		.show();
}
function loadNavInfo(node) {
	var isLast = TestObject.isLastQuestion();
	if (TestObject.options.get('navigationMode') == 2 && !isLast) {
		setButton('next', function() { skip_question(); return false; }, 'РџСЂРѕРїСѓСЃС‚РёС‚СЊ РІРѕРїСЂРѕСЃ');
	} else {
		setButton('next');
	}

	if (TestObject.options.get('navigationMode') == 1 && TestObject.getPreviousQuestion()) {
		setButton('previous', function() { previous_question(); return false; }, 'Р’РµСЂРЅСѓС‚СЊСЃСЏ Рє РїСЂРµРґС‹РґСѓС‰РµРјСѓ РІРѕРїСЂРѕСЃСѓ');
	} else {
		setButton('previous');
	}
	setButton('answer', function() { check_question(); return false; }, isLast ? 'РћС‚РІРµС‚РёС‚СЊ Рё Р·Р°РІРµСЂС€РёС‚СЊ С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ' : 'РћС‚РІРµС‚РёС‚СЊ');

	$(window).trigger('resize');
}
function ling_format_time(time, left) {
	var N = Math.floor(time / 1000 / 60);
	if (N == 0) {
		N = Math.floor(time / 1000);
		if ( (N % 100 >= 11 && N % 100 <= 19) || (N % 10 >= 5 && N % 10 <= 9) || N % 10 == 0 ) {
			return (left ? 'РѕСЃС‚Р°Р»РѕСЃСЊ ' : 'РїСЂРѕС€Р»Рѕ ')+ N +' СЃРµРєСѓРЅРґ';
		} else if (N % 10 == 1) {
			return (left ? 'РѕСЃС‚Р°Р»Р°СЃСЊ ' : 'РїСЂРѕС€Р»Р° ')+ N +' СЃРµРєСѓРЅРґР°';
		} else {
			return (left ? 'РѕСЃС‚Р°Р»РѕСЃСЊ ' : 'РїСЂРѕС€Р»Рѕ ')+ N +' СЃРµРєСѓРЅРґС‹';
		}
	} else {
		if ( (N % 100 >= 11 && N % 100 <= 19) || (N % 10 >= 5 && N % 10 <= 9) || N % 10 == 0 ) {
			return (left ? 'РѕСЃС‚Р°Р»РѕСЃСЊ ' : 'РїСЂРѕС€Р»Рѕ ')+ N +' РјРёРЅСѓС‚';
		} else if (N % 10 == 1) {
			return (left ? 'РѕСЃС‚Р°Р»Р°СЃСЊ ' : 'РїСЂРѕС€Р»Р° ')+ N +' РјРёРЅСѓС‚Р°';
		} else {
			return (left ? 'РѕСЃС‚Р°Р»РѕСЃСЊ ' : 'РїСЂРѕС€Р»Рѕ ')+ N +' РјРёРЅСѓС‚С‹';
		}
	}
}

// Р—Р°РіСЂСѓР·РєР° СЌР»РµРјРµРЅС‚РѕРІ
function loadingProxy() {
	eAu.contentBlocker.enable();
	load_test();
	return;
}

function load_test() {
	if (!Xml || !TestNode) {
		eLS.utils.showMessageBox('РћС€РёР±РєР° Р·Р°РіСЂСѓР·РєРё С‚РµСЃС‚Р°', 'РќРµ СѓРґР°Р»РѕСЃСЊ Р·Р°РіСЂСѓР·РёС‚СЊ С‚РµСЃС‚');
		return;
	}
	// РҐРђРљ, С‡С‚Рѕ-Р±С‹ РЅРµ РїР»РѕРґРёС‚СЊ if'С‹ РІ РєРѕРґРµ С„РѕСЂРјРёСЂРѕРІР°РЅРёСЏ
	// action'РѕРІ
	TestNode.setAttribute('type', 'test');
	// TODO: РїСЂРѕРІРµСЂРёС‚СЊ СЂР°Р±РѕС‚Сѓ!!!
	if (QParentParams['launched-by-tid'] && window.parent.parent && window.parent.parent.tracker) {
		window.parent.parent.tracker.action(TestNode);
	}
	TestObject = new Test(TestNode);
	if (!TestObject.getCurrentQuestion()) {
		eLS.utils.showMessageBox('РћС€РёР±РєР° Р·Р°РіСЂСѓР·РєРё С‚РµСЃС‚Р°', 'РќРµ РЅР°Р№РґРµРЅРѕ РЅРё РѕРґРЅРѕРіРѕ РІРѕРїСЂРѕСЃР°');
		return;
	}
	if (TestObject.options.get('timeLimit')) {
		window.testTimer = new eAu.Timer(TestObject.options.get('timeLimit'), {
			finish: function() {
				timer_test_finish();
			},
			callback: function(duration) {
				$('#eAu-test-time').html(ling_format_time(duration, true));
			}
		});
	} else {
		window.testInterval = setInterval(function() {
			$('#eAu-test-time').html(ling_format_time((new Date()).getTime() - TestObject.result.get('startTime')));
		}, 100);
	}
	load_question();
}

function interrupt_question_timer() {
	if (window.questionTimer) {
		window.questionTimer.interrupt();
		window.questionTimer = null;
	}
}
function interrupt_test_timer() {
	if (window.testTimer) {
		window.testTimer.interrupt();
		window.testTimer = null;
	}
}

// Р—Р°РІРµСЂС€РёС‚СЊ С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ РЅРѕСЂРјР°Р»СЊРЅС‹Рј РѕР±СЂР°Р·РѕРј
function finish_test(finishMode) {
	window.testDataSaved = true;
	interrupt_question_timer();
	interrupt_test_timer();

	window.testFinished = true;

	setButton('next');
	setButton('previous');
	setButton('answer');

	finishMode = finishMode || 1;
	
	TestObject.finishTest(finishMode);

	$('#cont').html('');
	
	var qs = TestObject.AllQuestionsHash;
	var tqs = TestObject.TestQuestions;
	// РЎРѕР±СЂР°С‚СЊ РґР°РЅРЅС‹Рµ РїРѕ С‚РµСЃС‚РёСЂРѕРІР°РЅРёСЋ
	var _xml = [];
	for (var i = 0; i < tqs.length; ++i) {
		var qId = tqs[i];
		var Q =  qs.get(qId);
		if (Q.get('tryesCount') <= 0) {
			_xml.push('<question DB_ID="'+ Q.get('id') +'" tryes="'+ Q.get('tryesCount') +'" />');
			continue;
		}
		_xml.push('<question\
\n      DB_ID      ="'+ Q.get('id') +'"\
\n      type       ="'+ Q.get('type') +'"\
\n      start_time ="'+ Q.get('lastTryStartTime') +'"\
\n      end_time   ="'+ Q.get('lastTryEndTime') +'"\
\n      score      ="'+ Q.get('score') +'"\
\n      tryes      ="'+ Q.get('tryesCount') +'"\
\n      balmax     ="'+ Q.get('scoreMax') +'"\
\n      balmin     ="'+ Q.get('scoreMin') +'">'
		);
		
		var lastTry = Q.get('lastTry');
		var ALength = lastTry ? lastTry.length : 0;
		for (var j = 0; j < ALength; ++j) {
			var a_xml = ['<answer'];
			var nodeValue = '';
			$H(lastTry[j]).each(function(kv) {
				if (kv.key.startsWith('!')) {
					nodeValue += kv.value;
				} else {
					a_xml.push(kv.key + '="'+ kv.value +'"');
				}
			});
			_xml.push('  '+ a_xml.join(' ') +'><![CDATA['+ nodeValue +']]></answer>');
		}
		if (Q.get('comment')) {
			_xml.push('  <comment><![CDATA['+ Q.get('comment') +']]></comment>');
		}
		_xml.push('</question>');
	}

	if (finishMode != 3 && finishMode != 2) {
		if (TestObject.options.get('showResults')) {
			var score = 0;
			var scoreMax = 0;
			var totalAnswered = 0;
			for (var i = 0; i < TestObject.TestQuestions.length; ++i) {
				var QId = TestObject.TestQuestions[i];
				var Q = TestObject.AllQuestionsHash.get(QId);
				if (!Object.isUndefined(Q.get('score'))) {
					totalAnswered++;
					score += Q.get('score');
				}
				scoreMax += Q.get('scoreMax');

				score = Math.round(score * 100) / 100;
			}
			if (window.parent.parent && window.parent.parent.tracker) {
				window.parent.parent.tracker.actionSetData(TestNode, {'score': score});
			}
			$('#cont').html('<h1 class="eAu">Р’С‹ Р·Р°РІРµСЂС€РёР»Рё С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ</h1>\
				<table>\
					<tr>\
						<td><h2 class="eAu">РљРѕР»РёС‡РµСЃС‚РІРѕ РѕС‚РІРµС‡РµРЅРЅС‹С… РІРѕРїСЂРѕСЃРѕРІ:</h2></td>\
						<td><h2 class="eAu">'+ totalAnswered +'</h2></td>\
					</tr>\
					<tr>\
						<td><h2 class="eAu">РќР°Р±СЂР°РЅРЅС‹Р№ Р±Р°Р»Р»:</h2></td>\
						<td><h2 class="eAu">'+ score +'</h2></td>\
					</tr>\
					<tr>\
						<td><h2 class="eAu">РњР°РєСЃРёРјР°Р»СЊРЅРѕ РІРѕР·РјРѕР¶РЅС‹Р№ Р±Р°Р»Р»:</h2></td>\
						<td><h2 class="eAu">'+ scoreMax +'</h2></td>\
					</tr>\
				</table>'
				+ (QParentParams['launched-by-tid'] ? '<p><a target="_top" href="../index.html">Р’РµСЂРЅСѓС‚СЊСЃСЏ Рє СЂР°СЃРїРёСЃР°РЅРёСЋ</a></p>' : '<p>Р’С‹Р±РµСЂРёС‚Рµ РѕС‡РµСЂРµРґРЅРѕР№ СЂР°Р·РґРµР» РІ РѕРіР»Р°РІР»Р°РІР»РµРЅРёРё РєСѓСЂСЃР° РґР»СЏ РїСЂРѕРґРѕР»Р¶РµРЅРёСЏ РѕР±СѓС‡РµРЅРёСЏ</p>')
			);
		} else {
			$('#cont').html('<h1 class="eAu">Р’С‹ Р·Р°РІРµСЂС€РёР»Рё С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ</h1>' + (QParentParams['launched-by-tid'] ? '<p><a target="_top" href="../index.html">Р’РµСЂРЅСѓС‚СЊСЃСЏ Рє СЂР°СЃРїРёСЃР°РЅРёСЋ</a></p>' : '<p>Р’С‹Р±РµСЂРёС‚Рµ РѕС‡РµСЂРµРґРЅРѕР№ СЂР°Р·РґРµР» РІ РѕРіР»Р°РІР»Р°РІР»РµРЅРёРё РєСѓСЂСЃР° РґР»СЏ РїСЂРѕРґРѕР»Р¶РµРЅРёСЏ РѕР±СѓС‡РµРЅРёСЏ</p>'));
		}
	} else if (finishMode == 3) {
		$('#cont').html('<h1 class="eAu">Р’С‹ РїСЂРµСЂРІР°Р»Рё С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ</h1>' + (QParentParams['launched-by-tid'] ? '<p><a target="_top" href="../index.html">Р’РµСЂРЅСѓС‚СЊСЃСЏ Рє СЂР°СЃРїРёСЃР°РЅРёСЋ</a></p>' : '<p>Р’С‹Р±РµСЂРёС‚Рµ РѕС‡РµСЂРµРґРЅРѕР№ СЂР°Р·РґРµР» РІ РѕРіР»Р°РІР»Р°РІР»РµРЅРёРё РєСѓСЂСЃР° РґР»СЏ РїСЂРѕРґРѕР»Р¶РµРЅРёСЏ РѕР±СѓС‡РµРЅРёСЏ</p>'));
	}
	if (window.parent.parent && window.parent.parent.tracker) {
		window.parent.parent.tracker.actionXmlData(TestNode, _xml.join('\n'));
		window.parent.parent.tracker.actionSetData(TestNode, {'end_time': (new Date()).getTime()});
	}
}
// РџСЂРµСЂРІР°С‚СЊ С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ
function break_test() {
	finish_test(3);
	eLS.utils.showMessageBox('Р’Р°С€Рµ С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ РїСЂРµСЂРІР°РЅРѕ');
}
// Р”РѕСЃСЂРѕС‡РЅРѕ Р·Р°РІРµСЂС€РёС‚СЊ С‚РµСЃС‚РёСЂРѕРІР°РЅРёРµ СЃ РІС‹СЃС‚Р°РІР»РµРЅРёРµРј РѕС†РµРЅРєРё
function end_test() {
	if (!TestObject.options.get('skip')) { return; }

	finish_test(4);
}
// РўРµСЃС‚РёСЂРѕРІР°РЅРёРµ Р·Р°РІРµСЂС€РµРЅРѕ РїРѕ РёСЃС‚РµС‡РµРЅРёРё РѕС‚РІРµРґС‘РЅРЅРѕРіРѕ РІСЂРµРјРµРЅРё
function timer_test_finish() {
	finish_test(5);
}
//РўРµСЃС‚РёСЂРѕРІР°РЅРёРµ Р±С‹Р»Рѕ Р±СЂРѕС€РµРЅРѕ, СѓС‡Р°С‰РёР№СЃСЏ Р·Р°РєСЂС‹Р» Р±СЂР°СѓР·РµСЂ
function test_unloaded() {
	finish_test(2);
}
window.onbeforeunload = function() {
	if (!TestObject) { return; }
	if (!window.testDataSaved) {
		test_unloaded();
	}
}
$(window).bind('unload', window.onbeforeunload);

// РџСЂРѕРїСѓСЃС‚РёС‚СЊ РІРѕРїСЂРѕСЃ
function skip_question() {
	// РџРµСЂРµС„РѕСЂРјРёСЂРѕРІР°С‚СЊ РѕС‡РµСЂРµРґСЊ
	// РІС‹РїРѕР»РЅРёС‚СЊ load_question
	interrupt_question_timer();
	TestObject.skipQuestion();
	load_question();
}
// РџРµСЂРµР№С‚Рё Рє РїСЂРµРґС‹РґСѓС‰РµРјСѓ РІРѕРїСЂРѕСЃСѓ
function previous_question() {
	interrupt_question_timer();
	TestObject.previousQuestion();
	load_question();
}
// СѓС‡Р°С‰РёР№СЃСЏ РЅРµ СѓСЃРїРµР» РѕС‚РІРµС‚РёС‚СЊ РЅР° РІРѕРїСЂРѕСЃ
function timer_question_finish() {
	check_question(true);
}
// РЈС‡Р°С‰РёР№СЃСЏ РѕС‚РІРµС‚РёР» РЅР° РІРѕРїСЂРѕСЃ, Р·Р°СЃС‡РёС‚Р°С‚СЊ РїРѕРїС‹С‚РєСѓ
function check_question(skip_validness_check) {
	// TODO: РїСЂРѕРІРµСЂРёС‚СЊ РїРѕР»СЏ РІРІРѕРґР°
	if (!skip_validness_check && TestObject.getCurrentQuestion().get('type') == 'single') {
		var userHasAnswered = $('input:checked').length != 0;
		if (!userHasAnswered) {
			eLS.utils.showMessageBox('РџСЂРµРґСѓРїСЂРµР¶РґРµРЅРёРµ', 'Р’С‹ РЅРµ РІС‹Р±СЂР°Р»Рё РІР°СЂРёР°РЅС‚ РѕС‚РІРµС‚Р°');
			return;
		}
	}
	interrupt_question_timer();

	TestObject.lastTryEndTime = (new Date()).getTime();

	var result = score_question();

	if (TestObject.getCurrentQuestion().get('type') == 'free') {
		var qId = 'EU-'+ TestObject.getCurrentQuestion().get('id') +'-TextArea';
		var tData = {};
		tData[qId] = document.getElementById(qId).value;
		TestObject.getCurrentQuestion().set('htmlSerializedForm', tData);
	} else {
		TestObject.getCurrentQuestion().set('htmlSerializedForm', $P('eAu-question-form').serialize(true));
	}
	// РЈРґР°Р»СЏРµРј СЌР»РµРјРµРЅС‚ РёР· HTML Dom
	$P('some-question-content').discard();

	if (!TestObject.isLastQuestion()) {
		TestObject.nextQuestion();
		load_question();
	} else {
		finish_test();
	}
}
function load_question() {
	loadNavInfo();
	var node = TestObject.getCurrentQuestion().get('xmlNode');

	$('#cont').html('<div style="text-align: center;" id="some-question-content"><img src="images/ajax-loader.gif" align="absmiddle" style="margin-right: 1em;" /><h2 style="display: inline;">Р—Р°РіСЂСѓР·РєР° РІРѕРїСЂРѕСЃР°...</h2></div>')
	disableButtons();
	setTimeout(function() {
		text_transform('<div id="some-question-content">'+ _X.transformToText(node, Xsl) +'</div>', 'cont');

		if (!TestObject.getCurrentQuestion().get('group') && !TestObject.getCurrentQuestion().get('timeLimit')) {
			$('#eAu-question-statistics').hide();
		}

		if (TestObject.getCurrentQuestion().get('timeLimit')) {
			window.questionTimer = new eAu.Timer(TestObject.getCurrentQuestion().get('timeLimit'), {
				finish: function() {
					timer_question_finish();
				},
				callback: function(duration) {
					$('#eAu-question-time').html(ling_format_time(duration, true));
				}
			});
		}

		$('#eAu-test-questions-statistics').html('РІРѕРїСЂРѕСЃРѕРІ РІСЃРµРіРѕ &ndash; <span class="eAu-test-questions-all">'+ TestObject.TestQuestions.length +'</span>, РѕСЃС‚Р°Р»РѕСЃСЊ &ndash; <span class="eAu-test-questions-left">'+ (TestObject.TestQuestionsQueue.length - TestObject.__currentPosition) +'</span>');

		if (TestObject.options.get('skip')) {
			$('#eAu-test-end').show();
		}

		enableButtons();

		var serializedForm = TestObject.getCurrentQuestion().get('htmlSerializedForm');
		if (serializedForm) {
			$H(serializedForm).each(function(pair) {
				var el = $P(pair.key);
				// IE also gets element by name
				if (el && el.id == pair.key) {
					el.value = pair.value;
				} else {
					el = $P(pair.key + '-' + pair.value);
					el.checked = true;
				}
			});
		}
		if (TestObject.getCurrentQuestion().get('comment')) {
			document.getElementById('EU-'+ TestObject.getCurrentQuestion().get('id') +'-TextArea').value = TestObject.getCurrentQuestion().get('comment');
		}
		TestObject.lastTryStartTime = (new Date()).getTime();
	}, 300);
}
/* END: Р—Р°РіСЂСѓР·РєР° СѓС‡РµР±РЅС‹С… РјРѕРґСѓР»РµР№ */

function score_question() {
	var question = TestObject.getCurrentQuestion();
	var userAnswers = [];
	var questionType = question.get('type');
	var answers = question.get('xmlNode').getElementsByTagName('answer');
	var scoreMin = question.get('scoreMin');
	var scoreMax = question.get('scoreMax');
	var score = 0;
	var tryesCount = question.get('tryesCount') + 1;
	
	var origScoreMin = scoreMin;

	// РќРѕСЂРјР°Р»РёР·СѓРµРј РїСЂРѕРјРµР¶СѓС‚РѕРє С‡С‚Рѕ-Р±С‹ РѕРЅ Р»РµР¶Р°Р» РІ РїСЂРµРґРµР»Р°С…
	// РѕС‚ РЅСѓР»СЏ РґРѕ ...
	scoreMax = scoreMax - origScoreMin;
	scoreMin = 0;

	// !!!!!!!!TODO!!!!!!!! - СЃРѕРіР»Р°СЃРѕРІР°С‚СЊ С„РѕСЂРјСѓР»С‹

	if (/compare/.test(questionType)) {
		var RightAnswersCount = 0;
		for (var i = 0; i < answers.length; ++i) {
			var answerId = _X.id(answers[i]);
			var answerNode = document.getElementById('EU-'+ answerId);
			var userAnswer = $(answerNode.options[answerNode.selectedIndex]).attr('val');
			var rightAnswer = _X.attr(answers[i], 'right');
			if (answerNode.selectedIndex >= 0 && (rightAnswer == userAnswer)) {
				RightAnswersCount++;
			}
			userAnswers.push({
				DB_ID: answerId,
				value: (answerNode.value || '').replace("EU-VAL-", "")
			});
		}
		score = scoreMax * parseFloat(RightAnswersCount) / parseFloat(answers.length);
	} else if (/fill/.test(questionType)) {
		var RightAnswersCount = 0;
		for (var i = 0; i < answers.length; ++i) {
			var answerId = _X.id(answers[i]);
			var answerNode = document.getElementById('EU-'+ answerId);
			var userAnswer = (answerNode.value || '').strip().norm();
			var rightAnswer = (_X.attr(answers[i], 'right') || '').strip();
			rightAnswer = rightAnswer
				.split(';')
				.map(function(val) {
					return val.strip().norm().toLowerCase();
				})
				.without('');
			if (rightAnswer.length == 0) {
				rightAnswer.push('');
			}
			if (rightAnswer.indexOf(userAnswer.toLowerCase()) != -1) {
				RightAnswersCount++;
			}
			userAnswers.push({
				DB_ID: answerId,
				value: userAnswer
			});
		}
		score = scoreMax * parseFloat(RightAnswersCount) / parseFloat(answers.length);
	} else if (/single|multiple/.test(questionType)) {
		var hasWeights = question.get('hasWeights');
		var AnswersCount = {
			right: { checked: 0, unchecked: 0 },
			wrong: { checked: 0, unchecked: 0 },
			weight: 0
		};
		for (var i = 0; i < answers.length; ++i) {
			var answerId = _X.id(answers[i]);
			var answerNodeId = 'EU-' + question.get('id') + '-' + (questionType == 'single' ? (i+1) : (answerId + '-1'))
			var answerNode = document.getElementById(answerNodeId);
			var answerWeight = parseInt(_X.attr(answers[i], 'weight'), 10) || 0;
			var userAnswer = answerNode.checked;
			var rightAnswer = _X.attr(answers[i], 'type') == 'true' ? true : false;

			if (rightAnswer == true) {
				userAnswer == true ? ++AnswersCount.right.checked : ++AnswersCount.right.unchecked;
			} else {
				userAnswer == true ? ++AnswersCount.wrong.checked : ++AnswersCount.wrong.unchecked;
			}
			if (!userAnswer) { continue; }

			AnswersCount.weight += answerWeight;
			var ua = { DB_ID: answerId };
			if (hasWeights) { ua.weight = answerWeight; }
			userAnswers.push(ua);
		}
		if (question.get('hasWeights')) {
			score = AnswersCount.weight;
		} else {
			// TODO: РјРѕР¶РµС‚ РґР°С‚СЊ РѕС‚СЂРёС†Р°С‚РµР»СЊРЅС‹Рµ Р·РЅР°С‡РµРЅРёСЏ?
			if (/multiple/.test(questionType)) {
				var koefficient = AnswersCount.right.checked / (AnswersCount.right.checked + AnswersCount.right.unchecked) -
					1.0 + (AnswersCount.right.checked + AnswersCount.right.unchecked + AnswersCount.wrong.unchecked) / answers.length;
				score = koefficient * (scoreMax);
			} else {
				score = AnswersCount.right.checked == 1 ? scoreMax : scoreMin;
			}
		}
	} else if (/table/.test(questionType)) {
		var NumberOfQuestions = 0;
		var NumberOfAnswers = 0;
		var AnswersWeight = 0;
		for (var i = 0; i < answers.length; ++i) {
			if (!_X.hasAttr(answers[i], 'right') || _X.attr(answers[i], 'right') == '') { continue; }
			var subQuestionId = _X.id(answers[i]);
			var answerNodes = document.getElementById('eAu-question-form')['EU-' + subQuestionId];
			var answerNode = answerNodes[0];
			for (var j = 0, length = answerNodes.length; j < length; ++j) {
				if (answerNodes[j].checked) {
					answerNode = answerNodes[j];
					AnswersWeight += parseInt(_X.attr(answerNode, 'weight'), 10) || 0;
					break;
				}
			}
			userAnswers.push($H({
				DB_ID: subQuestionId,
				number_of_question: parseInt(_X.attr(answerNode, 'question_n'), 10) - 1,
				number_of_answer: parseInt(_X.attr(answerNode, 'answer_n'), 10) - 1,
				weight: parseInt(_X.attr(answerNode, 'weight'), 10) || 0
			}));
		}
		var commentField = document.getElementById('EU-'+ question.get('id') +'-TextArea');
		if (commentField.value && commentField.value.strip()) {
			question.set('comment', commentField.value.strip());
		}
		score = AnswersWeight;
	} else if (/free/.test(questionType)) {
		var textField = document.getElementById('EU-'+ question.get('id') +'-TextArea');
		var val = textField.value && textField.value.strip()
			? textField.value.strip()
			: '';
		userAnswers.push({
			DB_ID: question.get('id'),
			'!answer': val
		});
	}
	
	score += origScoreMin;
	
	question.update({
		score: score,
		lastTry: userAnswers,
		tryesCount: tryesCount,
		lastTryStartTime: TestObject.lastTryStartTime,
		lastTryEndTime: TestObject.lastTryEndTime
	});
	
}
/* END: РІСЃРїРѕРјРѕРіР°С‚РµР»СЊРЅС‹Рµ С„СѓРЅРєС†РёРё С‚РµСЃС‚Р° */
