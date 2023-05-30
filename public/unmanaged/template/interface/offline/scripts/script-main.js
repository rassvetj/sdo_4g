var Test = Class.create({
	initialize: function(test) {
		this.test = test;
		this.AllQuestions = this.test.getElementsByTagName("question");

		// РџРѕР»СѓС‡Р°РµРј РЅР°СЃС‚СЂРѕР№РєРё С‚РµСЃС‚РёСЂРѕРІР°РЅРёСЏ
		this.__getOptions();
		this.__generateQuestionsHash();
		this.__generateTest();
		this.__currentPosition = 0;
		this.getNextQuestion();
		this.getPreviousQuestion();
		this.getCurrentQuestion();
		this.result = $H({
			startTime: new Date().getTime()
		});
	},
	finishTest: function(finishMode) {
		this.result.set('endTime', new Date().getTime());
	},
	getNextQuestion: function() {
		if (this.options.get('navigationMode') == 2 && this.TestQuestionsQueue.length == 1) {
			return this.getCurrentQuestion();
		}

		var QId = this.TestQuestionsQueue[this.__currentPosition + 1];
		if (Object.isUndefined(QId)) { return QId; }
		return this.AllQuestionsHash.get(QId);
	},
	getPreviousQuestion: function() {
		var QId = this.TestQuestionsQueue[this.__currentPosition - 1];
		if (Object.isUndefined(QId)) { return QId; }
		return this.AllQuestionsHash.get(QId);
	},
	getCurrentQuestion: function() {
		var QId = this.TestQuestionsQueue[this.__currentPosition];
		if (Object.isUndefined(QId)) { return QId; }
		return this.AllQuestionsHash.get(QId);
	},
	// Р•СЃР»Рё РІРѕРїСЂРѕСЃ РїРѕСЃР»РµРґРЅРёР№, СЌС‚Рѕ РЅРµ Р·РЅР°С‡РёС‚ С‡С‚Рѕ getNextQuestion РІРµСЂРЅС‘С‚ null
	isLastQuestion: function() {
		return this.__currentPosition == this.TestQuestionsQueue.length - 1;
	},
	// РџСЂРѕСЃС‚Рѕ РјРѕРґРёС„РёС†РёСЂСѓРµС‚ РѕС‡РµСЂРµРґСЊ
	// TODO: СЂРµР·СѓР»СЊС‚Р°С‚ РѕС‚РІРµС‚Р° РґРѕР»Р¶РЅР° СЃРѕС…СЂР°РЅСЏС‚СЊ РґСЂСѓРіР°СЏ С„СѓРЅРєС†РёСЏ
	nextQuestion: function() {
		// Р•СЃР»Рё Р·Р°РїСЂРµС‰С‘РЅ РІРѕР·РІСЂР°С‚ Рє РїСЂРµРґС‹РґСѓС‰РµРјСѓ РІРѕРїСЂРѕСЃСѓ, С‚Рѕ РЅРµ РёРјРµРµС‚ СЃРјС‹СЃР»Р°
		// С…СЂР°РЅРёС‚СЊ РµРіРѕ Id РІ РѕС‡РµСЂРµРґРё, РїСЂРѕСЃС‚Рѕ СѓРґР°Р»СЏРµРј РµРіРѕ
		if (this.options.get('navigationMode') != 1) {
			this.TestQuestionsQueue.shift();
			return;
		}
		// Р