<?php
//echo "<script>console.log(" . json_encode($_SESSION['s']['user']) . ")</script>";
$fullname = $_SESSION['s']['user']['lname'] . " " . $_SESSION['s']['user']['fname'] . " " . $_SESSION['s']['user']['patronymic'];
/* $groups_for_alert = array("Дружинина Виктория Павловна",
"Васильева Арина Львовна",
"Дегтярев Владислав Алексеевич",
"Заводчиков Данил Евгеньевич",
"Ибрагимов Одлар Мехман оглы",
"Ибрагимов Рим Абдулхакович",
"Костюк Григорий Александрович",
"Кулачков Илья Александрович",
"Рябов Александр Артемович",
"Сидоров Борис Федорович",
"Ломанов Максим Русланович",
"Воеводина Екатерина Юрьевна",
"Гасанова Фидан Рашад кызы",
"Чугункина Татьяна Игоревна",
"Бредихин Роман Сергеевич",
"Кафаров Ростислав Анатольевич",
"Стецюк Алина Кареновна",
"Андрианов Михаил Михайлович",
"Бурмистова Анастасия Олеговна",
"Грибова Елизавета Михайловна",
"Трубаева София Андреевна",
"Ференц Иван Олегович",
"Габидуллина Анетта Радиевна",
"Головченко Денис Кириллович",
"Скороходов Сергей Вадимович",
"Борщев Данил Дмитриевич",
"Алимов Михаил Александрович",
"Горохов Петр Александрович",
"Докучаев Святослав Вячеславович",
"Коротченко Антон Константинович",
"Пугачева Татьяна Владиславовна",
"Кузьмин Платон Алексеевич",
"Береза Константин Иванович",
"Воинов Дмитрий Игоревич",
"Гилев Михаил Владимирович",
"Голенков Антон Владимирович",
"Ильгов Вячеслав Иванович",
"Кожевников Алексей Владимирович",
"Костюк Павел Андреевич",
"Кулябин Максим Васильевич",
"Львов Дмитрий Николаевич",
"Любошиц Иммануил Борисович",
"Мазырко Владимир Александрович",
"Мустафаев Малик Султан Оглы",
"Наконечный Андрей Владимирович",
"Никульников Александр Викторович",
"Паюк Алексей Дмитриевич",
"Тимофеев Игорь Владимирович",
"Товмасян Давид Сурикович",
"Чернявский Олег Николаевич",
"Ерышева Кристина Ивановна",
"Лу Цянь",
"Чжан Цзияо",
"Морозов Николай Ильич",
"Картуесов Илья Сергеевич",
"Шматова Софья Шералиевна",
"Вэй Линь",
"Гао Юймин",
"Линь Мусэнь",
"Линь Юени",
"Лю Цзюньцин",
"Люсиков Владимир Владимирович",
"Матросов Александр Сергеевич",
"Со Фан",
"Сюй Жоши",
"У Сяохуа",
"Хань Фэн",
"Чжан Сяньхуа",
"Чжао Сюнь",
"Чугунов Вячеслав Сергеевич",
"Власенко Елена Денисовна",
"Голышева Екатерина Михайловна",
"Романьков Сергей Евгеньевич",
"Амерханов Ренат Рястемович",
"Егоров Павел Олегович",
"Кртян Сергей Петросович",
"Кузовкин Дмитрий Станиславович",
"Лысаков Сергей Владимирович",
"Янаков Александр Эдуардович",
"Евженко Вадим Сергеевич",
"Коростелев Никита Дмитриевич",
"Малова Дарья Ивановна",
"Куклин Андрей Владимирович",
"Баширова Елизавета Сергеевна",
"Ароян Артем Валерович",
"Сапунова Серафима Евгеньевна",
"Романова Эльвира Александровна",
"Чулкова Амира Сергеевна",
"Шибаева Полина Андреевна",
"Лобачев Филипп Романович",
"Стаханов Даниил Юрьевич",
"Серегин Александр Сергеевич",
"Шамсудинова Карина Родиновна",
"Степанова Екатерина  Дмитриевна",
"Горбова Елизавета Юрьевна",
"Петрова Анастасия Алексеевна",
"Александрова Анастасия Ивановна",
"Капустьян Алена Александровна",
"Орехова Дарья Михайловна",
"Карпикова Варвара Николаевна",
"Пашкова София Ивановна",
"Гарданова Марет Ахметовна",
"Корсикова Анна Вячеславовна",
"Мельников Павел Николаевич",
"Сакиев Асланбек Заурович",
"Черепанов Ярослав Максимович",
"Бекетов Илларион Николаевич",
"Иванов Давид Денисович",
"Магомедов Амир Джабраилович",
"Новак Гордей Алексеевич",
"Прохоров Иван Алексеевич",
"Агеенко Илья Сергеевич",
"Афанасьева Екатерина Олеговна",
"Бунтова Екатерина Сергеевна",
"Камчатова Яна Николаевна",
"Польская Екатерина Михайловна",
"Савенкова Полина Александровна",
"Экашаева Карина Альбертовна",
"Бычкова Мария Александровна",
"Лапкина Екатерина Викторовна",
"Прохорова Элина Альбертовна",
"Гусейнова Нурана Натиговна",
"Киреева Екатерина Алексеевна",
"Ерзова Александра Павловна",
"Ручкин Александр Сергеевич",
"Голеусова Екатерина Владимировна",
"Мамедова Хаяла Мушфиг кызы",
"Стукалова Александра Романовна",
"Кочетков Александр Евгеньевич",
"Ладур Альбина Рушановна",
"Орлова Елизавета Владимировна",
"Арутюнян Анна Араиковна",
"Даллакян Микаелла Грантовна",
"Белов Александр Андреевич",
"Болдов Сергей Алексеевич",
"Булкина Екатерина Валерьевна",
"Захаров Игорь Валерьевич",
"Клевалина Наталия Андреевна",
"Логинов Сергей Сергеевич",
"Марди Давид Зурабович",
"Сибиренков Дмитрий Андреевич",
"Сокоренко Алексей Станиславович",
"Шулика Олег Константинович",
"Зыкова Любовь Павловна",
"Симонова Алина Александровна",
"Бажукова Виолетта Андреевна",
"Пехотин Павел Павлович",
"Куралова Нургиза Кураловна",
"Карпунина Ксения Витальевна",
"Третьякова Анастасия Олеговна",
"Абышов Рагил Джабраилович",
"Гамаюнов Константин Алексеевич",
"Гурянов Канан Агилович",
"Дурновский Сергей Владимирович",
"Калабин Артем Евгеньевич",
"Ляпустин Илья Евгеньевич",
"Маннапов Ахмед Валиевич",
"Омаров Артур Арсенович",
"Пахомов Владимир Олегович",
"Прокудин Сергей Юрьевич",
"Саратовский Алексей Викторович",
"Устименко Милан Сергеевич",
"Шалунов Иван Васильевич",
"Байков Константин Александрович",
"Басманов Евгений Викторович",
"Бояджян Артем Робертович",
"Брун Михаил Евгеньевич",
"Вакуленко Евгений Борисович",
"Володин Дмитрий Олегович",
"Воронин Евгений Вячеславович",
"Гайдучек Дмитрий Владимирович",
"Голубев Сергей Александрович",
"Груничев Михаил Михайлович",
"Губайдулин Руслан Наилевич",
"Гужевкин Константин Сергеевич",
"Гук Александр Иванович",
"Домбаев Асламбек Салаудинович",
"Донцов Дмитрий Олегович",
"Дорофеев Алексей Михайлович",
"Дорофеев Антон Михайлович",
"Ентальцев Владимир Сергеевич",
"Зейналов Эльшад Эмин оглы",
"Зеленов Евгений Витальевич",
"Калинчук Алексей Константинович",
"Катырин Илья Сергеевич",
"Кириллов Кирилл Сергеевич",
"Клюшнев Юрий Александрович",
"Кругликов Сергей Андреевич",
"Кундузбаев Рустам Ахтямович",
"Любимов Антон Андреевич",
"Маштак Леонид Александрович",
"Мешкичев Алексей Викторович",
"Михайлов Александр Русланович",
"Моисеев Артём Викторович",
"Ницаков Вадим Сергеевич",
"Павловский Николай Игоревич",
"Петропольский Алексей Игоревич",
"Попов Никита Михайлович",
"Родин Олег Олегович",
"Рыбкин Дмитрий Михайлович",
"Свиридов Андрей Юрьевич",
"Семернин Олег Сергеевич",
"Тетеркин Владимир Юрьевич",
"Хабаров Денис Александрович",
"Халатов Лев Мисакович",
"Ханкерханов Мансур Мовладович",
"Ханкерханов Сайд-Ахмед Мовладиевич",
"Харитонов Кирилл Александрович",
"Хрипунов Иван Анатольевич",
"Цой Леонид Анатольевич",
"Цураев Салях Вахаевич",
"Чантиев Джамбулат Русланович",
"Шекиханов Максим Альбертович",
"Штутман Георгий Владимирович",
"Якубович Яков Борисович",
"Яринич Александр Валерьевич",
"Парса Ахмад Мусадек",
"Эбади Заид",
"Жданов Георгий Александрович",
"Зеленов Виталий Евгеньевич",
"Кочкин Роман Андреевич",
"Пожидаев Денис Георгиевич",
"Рассохо Денис Леонидович",
"Свиридов Денис Юрьевич",
"Бояркина Юлия Андреевна",
"Гузеева Алёна Дмитриевна",
"Метсамбо Косси Жеоси Жадор Габин",
"Мукулу-Нгембо Коотиа Ниширен",
"Фатехова Амина Наилевна",
"Баум Софья Кирилловна",
"Вершков Даниил Андреевич",
"Горин Даниил Михайлович",
"Комарова Наталия Сергеевна",
"Орлова Анна Кареновна",
"Аксенов Александр Михайлович",
"Бороденя Антонина Валерьевна",
"Гришин Денис Владимирович",
"Журавлев Станислав Валерьевич",
"Филиппова Валерия Андреевна",
"Мартынова Алина Ильинична",
"Гареева Екатерина Вадимовна",
"Матчиева Екатерина Александровна",
"Панина Анна Александровна",
"Суворова Ксения Игоревна",
"Эбзеева Саида Алимовна",
"Татищева Алина Анатольевна",
"Азаров Артём Николаевич",
"Горкин Даниил Олегович",
"Дацина Дмитрий Николаевич",
"Демин Андрей Сергеевич",
"Микляев Николай Вячеславович",
"Мкртычев Норик Ваноевич",
"Никашин Владимир Анатольевич",
"Пожидаев Станислав Георгиевич",
"Сапельченко Роман Валерьевич",
"Слободов Михаил Ефимович",
"Сухинин Антон Петрович",
"Чернов Денис Евгеньевич",
"Васильева Оксана Николаевна",
"Абашин Артем Дмитриевич",
"Арсланов Рустам Закиевич",
"Артемьев Сергей Петрович",
"Варков Илья Александрович",
"Гришин Константин Сергеевич",
"Делюкин Сергей Алексеевич",
"Жарков Игорь Владимирович",
"Ивашев Николай Александрович",
"Калинин Николай Константинович",
"Карпенко Александр Юрьевич",
"Лавров Александр Геннадьевич",
"Лашманкин Роман Игоревич",
"Лямин Дмитрий Владимирович",
"Маргелов Дмитрий Михайлович",
"Носков Виталий Николаевич",
"Орешков Алексей Сергеевич",
"Помытко Егор Александрович",
"Саттаров Дмитрий Ринатович",
"Ухловский Василий Валентинович",
"Царёв Михаил Александрович",
"Чернова Анна Александровна",
"Шамилов Альберт Изетович",
"Шмурнов Евгений Александрович",
"Щегольков Александр Михайлович",
"Бурнашев Тимур Ринатович",
"Дмитриев Андрей Юрьевич",
"Савенков Аркадий Вазгенович",
"Глотова Анастасия Олеговна",
"Каримова Арина Дамировна",
"Мельяновский Павел Дмитриевич",
"Мендеш Да Кошта Идалете Жуао",
"Плавинская Анастасия Игоревна",
"Полежайкина Екатерина Викторовна",
"Комарова Алина Андреевна",
"Пустоветова Александра Юрьевна",
"Стеценко Вероника Сергеевна",
"Шевлягина Дарья Дмитриевна",
"Пчелинова Вера Владимировна",
"Романова Анжела Валерьевна",
"Чеглаков Владислав Вадимович",
"Сафронова Анастасия Сергеевна",
"Сухарев Александр Юрьевич"); */
$groups_for_alert = array("Дружинина Виктория Павловна",
"Васильева Арина Львовна",
"Дегтярев Владислав Алексеевич",
"Заводчиков Данил Евгеньевич",
"Ибрагимов Одлар Мехман оглы",
"Ибрагимов Рим Абдулхакович",
"Костюк Григорий Александрович",
"Кулачков Илья Александрович",
"Рябов Александр Артемович",
"Сидоров Борис Федорович",
"Ломанов Максим Русланович",
"Воеводина Екатерина Юрьевна",
"Гасанова Фидан Рашад кызы",
"Чугункина Татьяна Игоревна",
"Бредихин Роман Сергеевич",
"Кафаров Ростислав Анатольевич",
"Стецюк Алина Кареновна",
"Андрианов Михаил Михайлович",
"Бурмистова Анастасия Олеговна",
"Грибова Елизавета Михайловна",
"Трубаева София Андреевна",
"Ференц Иван Олегович",
"Габидуллина Анетта Радиевна",
"Головченко Денис Кириллович",
"Скороходов Сергей Вадимович",
"Борщев Данил Дмитриевич",
"Алимов Михаил Александрович",
"Горохов Петр Александрович",
"Докучаев Святослав Вячеславович",
"Коротченко Антон Константинович",
"Пугачева Татьяна Владиславовна",
"Кузьмин Платон Алексеевич",
"Береза Константин Иванович",
"Воинов Дмитрий Игоревич",
"Гилев Михаил Владимирович",
"Голенков Антон Владимирович",
"Ильгов Вячеслав Иванович",
"Кожевников Алексей Владимирович",
"Костюк Павел Андреевич",
"Кулябин Максим Васильевич",
"Львов Дмитрий Николаевич",
"Любошиц Иммануил Борисович",
"Мазырко Владимир Александрович",
"Мустафаев Малик Султан Оглы",
"Наконечный Андрей Владимирович",
"Никульников Александр Викторович",
"Паюк Алексей Дмитриевич",
"Тимофеев Игорь Владимирович",
"Товмасян Давид Сурикович",
"Чернявский Олег Николаевич",
"Ерышева Кристина Ивановна",
"Лу Цянь",
"Чжан Цзияо",
"Морозов Николай Ильич",
"Картуесов Илья Сергеевич",
"Шматова Софья Шералиевна",
"Вэй Линь",
"Гао Юймин",
"Линь Мусэнь",
"Линь Юени",
"Лю Цзюньцин",
"Люсиков Владимир Владимирович",
"Матросов Александр Сергеевич",
"Со Фан",
"Сюй Жоши",
"У Сяохуа",
"Хань Фэн",
"Чжан Сяньхуа",
"Чжао Сюнь",
"Чугунов Вячеслав Сергеевич",
"Власенко Елена Денисовна",
"Голышева Екатерина Михайловна",
"Романьков Сергей Евгеньевич",
"Амерханов Ренат Рястемович",
"Егоров Павел Олегович",
"Кртян Сергей Петросович",
"Кузовкин Дмитрий Станиславович",
"Лысаков Сергей Владимирович",
"Янаков Александр Эдуардович",
"Евженко Вадим Сергеевич",
"Коростелев Никита Дмитриевич",
"Малова Дарья Ивановна",
"Куклин Андрей Владимирович",
"Баширова Елизавета Сергеевна",
"Ароян Артем Валерович",
"Сапунова Серафима Евгеньевна",
"Романова Эльвира Александровна",
"Чулкова Амира Сергеевна",
"Шибаева Полина Андреевна",
"Лобачев Филипп Романович",
"Стаханов Даниил Юрьевич",
"Серегин Александр Сергеевич",
"Шамсудинова Карина Родиновна",
"Степанова Екатерина  Дмитриевна",
"Горбова Елизавета Юрьевна",
"Петрова Анастасия Алексеевна",
"Александрова Анастасия Ивановна",
"Капустьян Алена Александровна",
"Орехова Дарья Михайловна",
"Карпикова Варвара Николаевна",
"Пашкова София Ивановна",
"Гарданова Марет Ахметовна",
"Корсикова Анна Вячеславовна",
"Мельников Павел Николаевич",
"Сакиев Асланбек Заурович",
"Черепанов Ярослав Максимович",
"Бекетов Илларион Николаевич",
"Иванов Давид Денисович",
"Магомедов Амир Джабраилович",
"Новак Гордей Алексеевич",
"Прохоров Иван Алексеевич",
"Агеенко Илья Сергеевич",
"Афанасьева Екатерина Олеговна",
"Бунтова Екатерина Сергеевна",
"Камчатова Яна Николаевна",
"Польская Екатерина Михайловна",
"Савенкова Полина Александровна",
"Экашаева Карина Альбертовна",
"Бычкова Мария Александровна",
"Лапкина Екатерина Викторовна",
"Прохорова Элина Альбертовна",
"Гусейнова Нурана Натиговна",
"Киреева Екатерина Алексеевна",
"Ерзова Александра Павловна",
"Ручкин Александр Сергеевич",
"Голеусова Екатерина Владимировна",
"Мамедова Хаяла Мушфиг кызы",
"Стукалова Александра Романовна",
"Кочетков Александр Евгеньевич",
"Ладур Альбина Рушановна",
"Орлова Елизавета Владимировна",
"Арутюнян Анна Араиковна",
"Даллакян Микаелла Грантовна",
"Белов Александр Андреевич",
"Болдов Сергей Алексеевич",
"Булкина Екатерина Валерьевна",
"Захаров Игорь Валерьевич",
"Клевалина Наталия Андреевна",
"Логинов Сергей Сергеевич",
"Марди Давид Зурабович",
"Сибиренков Дмитрий Андреевич",
"Сокоренко Алексей Станиславович",
"Шулика Олег Константинович",
"Зыкова Любовь Павловна",
"Симонова Алина Александровна",
"Бажукова Виолетта Андреевна",
"Пехотин Павел Павлович",
"Куралова Нургиза Кураловна",
"Карпунина Ксения Витальевна",
"Третьякова Анастасия Олеговна",
"Абышов Рагил Джабраилович",
"Гамаюнов Константин Алексеевич",
"Гурянов Канан Агилович",
"Дурновский Сергей Владимирович",
"Калабин Артем Евгеньевич",
"Ляпустин Илья Евгеньевич",
"Маннапов Ахмед Валиевич",
"Омаров Артур Арсенович",
"Пахомов Владимир Олегович",
"Прокудин Сергей Юрьевич",
"Саратовский Алексей Викторович",
"Устименко Милан Сергеевич",
"Шалунов Иван Васильевич",
"Байков Константин Александрович",
"Басманов Евгений Викторович",
"Бояджян Артем Робертович",
"Брун Михаил Евгеньевич",
"Вакуленко Евгений Борисович",
"Володин Дмитрий Олегович",
"Воронин Евгений Вячеславович",
"Гайдучек Дмитрий Владимирович",
"Голубев Сергей Александрович",
"Груничев Михаил Михайлович",
"Губайдулин Руслан Наилевич",
"Гужевкин Константин Сергеевич",
"Гук Александр Иванович",
"Домбаев Асламбек Салаудинович",
"Донцов Дмитрий Олегович",
"Дорофеев Алексей Михайлович",
"Дорофеев Антон Михайлович",
"Ентальцев Владимир Сергеевич",
"Зейналов Эльшад Эмин оглы",
"Зеленов Евгений Витальевич",
"Калинчук Алексей Константинович",
"Катырин Илья Сергеевич",
"Кириллов Кирилл Сергеевич",
"Клюшнев Юрий Александрович",
"Кругликов Сергей Андреевич",
"Кундузбаев Рустам Ахтямович",
"Любимов Антон Андреевич",
"Маштак Леонид Александрович",
"Мешкичев Алексей Викторович",
"Михайлов Александр Русланович",
"Моисеев Артём Викторович",
"Ницаков Вадим Сергеевич",
"Павловский Николай Игоревич",
"Петропольский Алексей Игоревич",
"Попов Никита Михайлович",
"Родин Олег Олегович",
"Рыбкин Дмитрий Михайлович",
"Свиридов Андрей Юрьевич",
"Семернин Олег Сергеевич",
"Тетеркин Владимир Юрьевич",
"Хабаров Денис Александрович",
"Халатов Лев Мисакович",
"Ханкерханов Мансур Мовладович",
"Ханкерханов Сайд-Ахмед Мовладиевич",
"Харитонов Кирилл Александрович",
"Хрипунов Иван Анатольевич",
"Цой Леонид Анатольевич",
"Цураев Салях Вахаевич",
"Чантиев Джамбулат Русланович",
"Шекиханов Максим Альбертович",
"Штутман Георгий Владимирович",
"Якубович Яков Борисович",
"Яринич Александр Валерьевич",
"Парса Ахмад Мусадек",
"Эбади Заид",
"Жданов Георгий Александрович",
"Зеленов Виталий Евгеньевич",
"Кочкин Роман Андреевич",
"Пожидаев Денис Георгиевич",
"Рассохо Денис Леонидович",
"Свиридов Денис Юрьевич",
"Бояркина Юлия Андреевна",
"Гузеева Алёна Дмитриевна",
"Метсамбо Косси Жеоси Жадор Габин",
"Мукулу-Нгембо Коотиа Ниширен",
"Фатехова Амина Наилевна",
"Баум Софья Кирилловна",
"Вершков Даниил Андреевич",
"Горин Даниил Михайлович",
"Комарова Наталия Сергеевна",
"Орлова Анна Кареновна",
"Аксенов Александр Михайлович",
"Бороденя Антонина Валерьевна",
"Гришин Денис Владимирович",
"Журавлев Станислав Валерьевич",
"Филиппова Валерия Андреевна",
"Мартынова Алина Ильинична",
"Гареева Екатерина Вадимовна",
"Матчиева Екатерина Александровна",
"Панина Анна Александровна",
"Суворова Ксения Игоревна",
"Эбзеева Саида Алимовна",
"Татищева Алина Анатольевна",
"Азаров Артём Николаевич",
"Горкин Даниил Олегович",
"Дацина Дмитрий Николаевич",
"Демин Андрей Сергеевич",
"Микляев Николай Вячеславович",
"Мкртычев Норик Ваноевич",
"Никашин Владимир Анатольевич",
"Пожидаев Станислав Георгиевич",
"Сапельченко Роман Валерьевич",
"Слободов Михаил Ефимович",
"Сухинин Антон Петрович",
"Чернов Денис Евгеньевич",
"Васильева Оксана Николаевна",
"Абашин Артем Дмитриевич",
"Арсланов Рустам Закиевич",
"Артемьев Сергей Петрович",
"Варков Илья Александрович",
"Гришин Константин Сергеевич",
"Делюкин Сергей Алексеевич",
"Жарков Игорь Владимирович",
"Ивашев Николай Александрович",
"Калинин Николай Константинович",
"Карпенко Александр Юрьевич",
"Лавров Александр Геннадьевич",
"Лашманкин Роман Игоревич",
"Лямин Дмитрий Владимирович",
"Маргелов Дмитрий Михайлович",
"Носков Виталий Николаевич",
"Орешков Алексей Сергеевич",
"Помытко Егор Александрович",
"Саттаров Дмитрий Ринатович",
"Ухловский Василий Валентинович",
"Царёв Михаил Александрович",
"Чернова Анна Александровна",
"Шамилов Альберт Изетович",
"Шмурнов Евгений Александрович",
"Щегольков Александр Михайлович",
"Бурнашев Тимур Ринатович",
"Дмитриев Андрей Юрьевич",
"Савенков Аркадий Вазгенович",
"Глотова Анастасия Олеговна",
"Каримова Арина Дамировна",
"Мельяновский Павел Дмитриевич",
"Мендеш Да Кошта Идалете Жуао",
"Плавинская Анастасия Игоревна",
"Полежайкина Екатерина Викторовна",
"Комарова Алина Андреевна",
"Пустоветова Александра Юрьевна",
"Стеценко Вероника Сергеевна",
"Шевлягина Дарья Дмитриевна",
"Пчелинова Вера Владимировна",
"Романова Анжела Валерьевна",
"Чеглаков Владислав Вадимович",
"Сафронова Анастасия Сергеевна",
"Дроздов Константин Алексеевич",
"Агулова Елена Александровна",
"Пустовалова Александра Сергеевна",
"Маслова Алёна Павловна",
"Свищев Артём Дмитриевич",
"Суворова Юлия Сергеевна",
"Чернецова Полина Александровна",
"Волков Даниил Александрович",
"Догонашева Ксения Алексеевна",
"Бубнова Александра Алексеевна",
"Калныш Юлия Сергеевна",
"Повалий Оксана Игоревна",
"Розанов Андрей Владимирович",
"Рыжова Екатерина Андреевна",
"Шугурова Анастасия Андреевна",
"Лысенко Владимир Денисович",
"Маркелов Иван Дмитриевич",
"Битаев Денис Николаевич",
"Лапин Глеб Алексеевич",
"Литвинов Даниил Романович",
"Мнацаканян Эдвард Гарикович",
"Новичкова Ангелина Владимировна",
"Садилов Дмитрий Игоревич",
"Сташин Никита Владимирович",
"Толкунов Матвей Андреевич",
"Толмачев Андрей Игоревич",
"Ярцев Дмитрий Альбертович",
"Ятов Владимир Ильич",
"Воробьева Анжела Игоревна",
"Воропаева Мария Григорьевна",
"Каторгин Максим Константинович",
"Кузнецов Дмитрий Николаевич",
"Ракамов Даниил Александрович",
"Чеканов Иван Романович",
"Климанов Кирилл Антонович",
"Лукашин Даниил Дмитриевич",
"Петухова Дарья Ивановна",
"Понамарев Максим Александрович",
"Козлов Андрей Дмитриевич",
"Федорец Андрей Андреевич",
"Иванова Елизавета Алексеевна",
"Кошкарихин Егор Александрович",
"Попов Данила Игоревич",
"Бикетов Алексей Вадимович",
"Петрушин Максим Сергеевич",
"Комаров Валерий Александрович",
"Кузнецова Александра Сергеевна",
"Грёнов Михаил Сергеевич",
"Филиппов Михаил Александрович",
"Горбушин Артур Дмитриевич",
"Гула Егор Александрович",
"Королева Мария Вадимовна",
"Лягин Никита Романович",
"Шахов Даниил Викторович",
"Воронин Евгений Викторович",
"Зиновин Сергей Анатольевич",
"Некторов Андрей Владимирович",
"Ненашев Сергей Александрович",
"Новиков Игорь Анатольевич",
"Цыплашов Олег Сергеевич",
"Чернышев Александр Юрьевич",
"Соловьева Светлана Владимировна",
"Баушер Юрий Васильевич",
"Буртман Инна Павловна",
"Куйдин Даниил Олегович",
"Рустамов Бехруз Рустамович",
"Стрельчук Никита Владимирович",
"Халилов Рустам Тегеранович",
"Бабазаде Орудж Юсиф оглы",
"Баттур Оюунжаргал",
"Ряннель Алина Юрьевна",
"Харлов Федор Александрович",
"Шилина Ксения Дмитриевна",
"Геворкян Геворк Андреевич",
"Ндонгала Гради Кени",
"Плотникова Екатерина Борисовна",
"Павлова Ярослава Сергеевна",
"Корнеева Вероника Александровна",
"Мильцева Анастасия Андреевна",
"Морева Екатерина Сергеевна",
"Николаев Егор Юрьевич",
"Сухорукова Елизавета Игоревна",
"Берус Олег Алексеевич",
"Горовенко Дарья Владимировна",
"Сергиеня Стефания Ильинична",
"Хамилонова Виктория Сергеевна",
"Водясов Даниил Антонович",
"Ликинцева Александра Александровна",
"Рубаков Андрей Владимирович",
"Мешков Андрей Павлович",
"Рогов Павел Михайлович",
"Чиндина Ксения Константиновна",
"Донской Пётр Александрович",
"Загребельная Анастасия Сергеевна",
"Мангуэ Дэйзи Маризиа",
"Мурашов Артём Андреевич",
"Русскина Елена Владимировна",
"Теляткин Владимир Сергеевич",
"Шерстобитова Ольга",
"Липатова Дарья Андреевна",
"Подсветов Кирилл Андреевич",
"Сидоренков Илья Сергеевич",
"Анненкова Ева Анатольевна",
"Виноградова Анна Сергеевна",
"Марараш Максим Сергеевич",
"Набиева Эльвира Алексеевна",
"Паель Валерия Евгеньевна",
"Булавина Дарья Сергеевна",
"Чвалова Арина Дмитриевна",
"Кокшарова Анастасия Тимуровна",
"Скородумова Регина Андреевна",
"Хоролец София Сергеевна",
"Кармицкий Кирилл Сергеевич",
"Кривошлыкова Екатерина Станиславовна",
"Бекбаев Алмаз Серикович",
"Булатова Полина Дмитриевна",
"Губайдуллин Айнур Ильдарович",
"Ильин Дмитрий Валерьевич",
"Сорокин Иван Дмитриевич",
"Хузеева Альфия Альбертовна",
"Черникова Александра Александровна",
"Илларионова Анастасия Константиновна",
"Янгичер Екатерина Эдуардовна",
"Золотарева Елена Сергеевна",
"Гузева Полина Дмитриевна",
"Авагян Артавазд Ашотович",
"Алеев Эльдар Аксянович",
"Байда Василий Николаевич",
"Безносов Платон Петрович",
"Быков Илья Александрович",
"Воличев Андрей Александрович",
"Воробьев Александр Иванович",
"Герасимов Андрей Вадимович",
"Гильманов Сергей Булатович",
"Дукарт Юрий Александрович",
"Емельянов Владимир Владимирович",
"Ефанов Иван Андреевич",
"Зюзин Сергей Александрович",
"Калмыков Владимир Дмитриевич",
"Караваев Павел Михайлович",
"Козырь Александр Алексеевич",
"Кузьмин Алексей Юрьевич",
"Куров Владимир Евгеньевич",
"Линденблот Денис Борисович",
"Лукьянов Дмитрий Сергеевич",
"Ляшенко Станислав Владимирович",
"Морозов Никита Вячеславович",
"Нагорный Вадим Игоревич",
"Наливкин Алексей Александрович",
"Некрасов Михаил Сергеевич",
"Палкин Илья Андреевич",
"Попов Алексей Александрович",
"Разяпов Ринат Юлаевич",
"Сеченов Александр Сергеевич",
"Столяров Александр Викторович",
"Сухарев Алексей Алексеевич",
"Трубников Василий Вячеславович",
"Чудинов Максим Васильевич",
"Юрченко Сергей Владимирович",
"Яковлев Евгений Евгеньевич",
"Сухарев Александр Юрьевич");
// "Сухарев Александр Юрьевич"
//echo "<script>console.log('" . $fullname . "')</script>";
//echo "<script>console.log('" . in_array($fullname, $groups_for_alert) . "')</script>";
if (in_array($fullname, $groups_for_alert)) {
	echo "<script src='https://code.jquery.com/jquery-3.6.3.min.js'></script>";
	echo "<script src='/js/1902alert.js'></script>";
}
?>