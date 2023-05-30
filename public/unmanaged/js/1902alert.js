function getCookie(name) {
  let matches = document.cookie.match(new RegExp(
	"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
setTimeout(function () {
if (getCookie('stud1702') != 110) {

	document.cookie = 'stud1702=110; max-age=3600';

	let div = document.createElement('div');
	div.className = 'myAgreement1';
	let shadow = document.createElement('div');
	shadow.id = 'myShadow1';
	div.append(shadow);
	let agreement = document.createElement('div');
	agreement.id = 'Agreement1';
	let myHeader = document.createElement('u');
	myHeader.id = 'myText';
	//myHeader.innerText = 'В соответствии с п. 36 «Инструкции об организации работы по обеспечению функционирования системы воинского учета», утвержденной приказом Министра обороны Российской Федерации от 22 ноября 2021 г. № 700, в РГСУ проводится сверка сведений воинского учета. На основании распоряжения первого проректора РГСУ от 29 декабря 2022 г. № 90-р (https://rgsu.net/for-students/navigator/voenno-uchetnyy-stol/) всем студентам надлежит прибыть во Второй отдел РГСУ и сверить свои данные воинского учета, при этом обращаем внимание на перечень документов необходимый для сверки (указан в п. 6). По итогам проведения сверки на военнообязанного гражданина оформляется карточка ф.10 с проставлением его личной подписи, подтверждающей сведения, указанные в ней. В дальнейшем указанные сведения будут сверены с учетными данными военных комиссариатов. Указанное мероприятие также направлено на оказание обучающимся практической помощи в вопросах приведения их документов воинского учета в соответствие с действующим законодательством Российской Федерации, а также реализации их права на получение отсрочки от призыва на военную службу. Лица, которые по объективным причинам не имеют возможности прибыть на сверку, направляют письменное уведомление с указанием причины их не явки с приложением подтверждающих документов на адрес электронной почты: DupelevAA@rgsu.net. Контактный телефон: Начальник Второго отдела РГСУ – Дупелев Андрей Анатольевич, 8 (495) 255-67-67, доб. 3051.';
	myHeader.innerText = 'Уважаемые студенты!';
	$(myHeader).css('margin-top', '40px');
	agreement.append(myHeader);

	let text = document.createElement('p');
	text.id = 'myText';
	text.innerText = '\u00A0\u00A0\u00A0\u00A0\u00A0В целях обеспечения безопасности людей и в связи с ужесточением требований по антитеррористической защищенности объектов со стороны Федеральных органов власти, '

	let spantext = document.createElement('span');
	spantext.id = 'myText';
	spantext.innerText = 'проход студентов на территорию Университета будет осуществляться только по бесконтактным (электронным) пропускам.';
	$(spantext).css('text-decoration', 'underline');
	text.append(spantext);
	$(text).css('text-align', 'justify')
	$(text).css('font-size', '14px');
	agreement.append(text);

	let myHeader2 = document.createElement('u');
	myHeader2.id = 'myText';
	myHeader2.innerText = 'Порядок оформления пропуска для  студентов РГСУ:';
	agreement.append(myHeader2);

	let text2 = document.createElement('p');
	text2.id = 'myText';
	text2.innerText = 'Обучающемуся  необходимо явиться в Управление безопасности Университета по адресу: \nул. Вильгельма Пика, дом 4, корпус 2  (1 этаж) кабинет 117\n(вход со стороны бассейна)\nПри себе иметь документ, удостоверяющий личность.';
	$(text2).css('text-align', 'center');
	$(text2).css('font-size', '13px');
	agreement.append(text2);

	let text3 = document.createElement('p');
	text3.id = 'myText';
	text3.innerText = 'Часы работы пункта оформления и выдачи пропусков:';
	$(text3).css('text-align', 'center');
	$(text3).css('font-weight', 'lighter');
	$(text3).css('font-size', '13px');
	agreement.append(text3);

	let text4 = document.createElement('p');
	text4.id = 'myText';
	text4.innerText = 'ПН-ЧТ с 09:00 до 18:00\nПТ с 09:00 до 16:45\nПерерыв с 13:00-14:00';
	$(text4).css('font-weight', 'lighter');
	$(text4).css('margin-left', '20px');
	$(text4).css('text-align', 'left');
	$(text4).css('font-size', '13px');
	agreement.append(text4);

	let text5 = document.createElement('p');
	text5.id = 'myText';
	text5.innerText = 'Информируем, что для удобства и по желанию, в качестве пропуска могут быть использованы следующие виды карт: \n1. карта Тройка, только без знака «V» на обратной стороне;\n2. социальная карта студента, срок действия которой - 2028 год или ранее;\n3. банковские карты – только карта Сбербанка «МИР».';
	$(text5).css('font-weight', 'lighter');
	$(text5).css('margin-left', '20px');
	$(text).css('text-align', 'justify')
	$(text5).css('font-size', '13px');
	agreement.append(text5);

	let textend = document.createElement('p');
	textend.id = 'myText';
	textend.innerText = 'Нет необходимости повторно являться в Управление безопасности студентам и сотрудникам, уже имеющим на руках действующий пропуск.';
	$(textend).css('text-align', 'center');
	$(textend).css('font-size', '13px');
	agreement.append(textend);

	let btn = document.createElement('button');
	btn.id = 'myBtn1';
	btn.innerText = 'Ознакомлен';

	agreement.append(btn);

	div.append(agreement);

	$('body').prepend(div);

	$('#myBtn1').click(function(){
		$('.myAgreement1').css('display', 'none');
	});
};
}, 5000);