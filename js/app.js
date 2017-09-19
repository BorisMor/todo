(function (window) {
    'use strict';

    /**
     * Объект отвечающий за авторизацию
     * @param parent Каркас приложения
     */
    var authorization = function (parent) {
        var self = this;

        // Родительский объект
        this.parent = parent;

        /**
         * Элементы интерфейса
         * @type {{el: undefined, inputLogin: undefined, inputPassword: undefined, buttonLogin: undefined}}
         */
        this.gui = {
            // корневой элемент
            el: undefined,
            // Поле логиа
            inputLogin: undefined,
            // Поле пароля
            inputPassword: undefined,
            // Кнопка вход
            buttonLogin: undefined
        };

        this.initialize = function () {
            self.el = $('.authorization');

            self.gui.inputLogin = self.el.find('input.login');
            self.gui.inputPassword = self.el.find('input.password');
            self.gui.buttonLogin = self.el.find('button.login');
            self.gui.error = self.el.find('.error');

            self.gui.buttonLogin.on('click', self.onClickButLogin);
            self.checkToken();
        };

        /**
         * Прячем дилог и показываем основное прилождение
         */
        this.hide = function () {
            self.el.hide();
            self.parent.gui.el.show();
            self.parent.gui.sectionFooter.show();
        };

        /**
         * Показываем диалог авторизации и запроса пароля
         */
        this.show = function () {
            self.el.show();
            self.parent.gui.el.hide();
            self.parent.gui.sectionFooter.hide();
        };

        /**
         * Проверка токена
         * Если в наличие то переходим в овносное приложение и вернет сам токен
         * @returns {undefined}
         */
        this.checkToken = function () {
            var token = localStorage.getItem('token');
            if (self.parent.empty(token)) {
                self.show();
                return undefined;
            } else {
                self.hide();
                return token;
            }
        };

        // Обоаботка кнопки на авторизацию
        this.onClickButLogin = function (e) {
            self.gui.error.html("");
            $.ajax({
                url: '/api/login',
                method: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    login: self.gui.inputLogin.val(),
                    password: self.gui.inputPassword.val()
                }),
                success: function (returnData, textStatus, jqXHR) {
                    if (!returnData.success) {
                        self.gui.error.html(returnData.data);
                        return;
                    }

                    localStorage.setItem('token', returnData.data);
                    self.checkToken();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    self.gui.error.html(jqXHR.responseJSON.data);
                    debugger;
                }
            })
        };

        /**
         * Разлогинится
         */
        this.logout = function () {
            self.parent.server('POST', '/api/logout', undefined, function (retData, textStatus, jqXHR) {
                localStorage.setItem('token', '');
                self.checkToken();
            })
        };

        this.initialize();
    };

    /**
     * объект отвечающий за одну строчку to-do листа
     * @param parent Родитель
     */
    var ItemTodo = function (parent) {
        var self = this;

        // Родительский объект
        this.parent = parent;

        // ID записи
        this.id = undefined;

        // Название
        this.title = undefined;

        // Признак выполненности
        this.completed = false;

        // Признак редактируемости
        this.editing = false;

        // DOM элемент который вывел данный объект
        this.dom = undefined;

        /**
         * GUI элементы
         * @type {{toggle: undefined, destroy: undefined}}
         */
        this.gui = {
            // Перекллючатель выполненного события
            toggle: undefined,
            // Кнопка удаления
            destroy: undefined,
            // Лэйбл с описанием
            label: undefined,
            // Input для редактирвоания title
            edit: undefined
        };

        /**
         * Иницилизацуия элементов интерфейса
         */
        this.initGUI = function () {
            self.gui.toggle = self.dom.find('input.toggle');
            self.gui.destroy = self.dom.find('button.destroy');
            self.gui.label = self.dom.find('label');
            self.gui.edit = self.dom.find('input.edit');

            // Обработчики событий
            self.gui.toggle.on('change', self.onChangeToggle);
            self.gui.destroy.on('click', self.onClickButtonDestroy);
            self.gui.label.on('dblclick', self.omDblclickLabel);

            self.gui.edit.on('keypress', self.onKeyPressEdit);
            self.gui.edit.on('keydown', self.onKeyDownEdit);
            self.gui.edit.on('blur', self.onBlurEdit);
        };

        /**
         * Выйти из режима редактирования
         */
        this.exitEditMode = function () {
            self.editing = false;
            self.update();
        };

        this.onKeyPressEdit = function (e) {
            // 13 = Enter
            if (e.which === 13) {
                self.title = self.gui.edit.val().trim();
                self.exitEditMode();
                self.saveDb();
            }
        };

        /**
         * Поле редактирования title потеряло фокус
         * @param e
         */
        this.onBlurEdit = function (e) {
            self.exitEditMode();
        };

        this.onKeyDownEdit = function (e) {
            // 27 = ESC
            if (e.which === 27) {
                self.exitEditMode();
            }
        };

        /**
         * Строка отрисовалась
         * @param dom
         */
        this.setDOM = function (dom) {
            self.dom = dom;
            self.initGUI();
        };

        // Обновить элемент
        this.update = function () {
            self.parent.render(self);
        };

        /**
         * Класс CSS для строки
         * @returns {string}
         */
        this.getClassCSS = function () {
            if (self.editing) {
                return 'editing';
            }

            if (self.completed) {
                return 'completed';
            }

            return '';
        };

        /**
         * Обработка переключения состояния
         * @param e
         */
        this.onChangeToggle = function (e) {
            self.completed = self.gui.toggle.prop('checked');
            self.update();
            self.updateDb();
        };

        /**
         * Обработка нажатия на кнопку удаления
         * @param e
         */
        this.onClickButtonDestroy = function (e) {
            self.deleteDb(function () {
                self.parent.deleteOneItem(self);
            });
        };

        // Перешли в режим редактирования при 2-ном клике
        this.omDblclickLabel = function (e) {
            self.editing = true;
            self.update();
        };

        /**
         * Основные атрибуты
         * @returns {{id: *, title: *, completed: (boolean|*)}}
         */
        this.getAttributes = function () {
            return {
                id: self.id,
                title: self.title,
                completed: self.completed
            }
        };

        /**
         * Удаление из базы
         * @param callback
         */
        this.deleteDb = function (callback) {
            self.parent.server('DELETE', '/api/item/' + self.id, undefined, function (retData, textStatus, jqXHR) {
                if (typeof callback === "function") {
                    callback();
                }
            });
        };

        /**
         * Добавить в БД
         * @param callback
         */
        this.insertDb = function (callback) {
            self.parent.server('PUT', '/api/item', self.getAttributes(), function (retData, textStatus, jqXHR) {
                self.id = retData.data.id;
                if (typeof callback === "function") {
                    callback();
                }
            });
        };

        /**
         * Обновить в БД
         */
        this.updateDb = function (callback) {
            self.parent.server('POST', '/api/item/' + self.id, self.getAttributes(), function (retData, textStatus, jqXHR) {
                if (typeof callback === "function") {
                    callback();
                }
            });
        };

        /**
         * Сохранить в БД
         * @param callback
         */
        this.saveDb = function (callback) {
            if (self.parent.empty(self.id)) {
                self.insertDb(callback)
            } else {
                self.updateDb(callback);
            }
        }
    };

    ////////////////////////////////////

    /**
     * Каркас приложения
     * @constructor
     */
    var AppTodo = function () {
        var self = this;

        // список задач
        this.items = [];
        // Фильтр выводимых задач по по статусу (undefined, false, true)
        this.filterCompleted = undefined;
        // Объект отвечающий за авторизацию
        this.authorization = undefined;

        // Шаблон строки
        this.templateItem = undefined;

        // элементы интерфейса
        this.gui = {
            // корневой элемент
            el: undefined,
            // Поля ввода для нового элемента
            newTodo: undefined,
            // Количество строк
            todoCount: undefined,
            // Кнопка удаляющее выполненное
            clearCompleted: undefined,
            // Главный раздел
            sectionMain: undefined,
            // Нижний раздел
            sectionFooter: undefined,
            // Поменять статус для всех значений
            toggleAll: undefined,
            // Разлогинится
            linkLogout: undefined
        };

        /**
         * Иницилизация
         */
        this.initialize = function () {
            var el = $('.todoapp');

            // --- Иницилизиурем элементы интерфейса
            self.gui.el = el;
            self.gui.newTodo = $('.new-todo', el);
            self.gui.todoList = $('.todo-list', el);
            self.gui.todoCount = $('.todo-count>strong', el);
            self.gui.clearCompleted = $('button.clear-completed', el);
            self.gui.sectionMain = $('section.main', el);
            self.gui.sectionFooter = $('footer.footer', el);
            self.gui.toggleAll = $('input.toggle-all', el);
            self.gui.linkLogout = $('a.logout', el);

            self.templateItem = _.template($('#template-item-view', el).html()); // шаблон строки

            // --- Иницилизируем обработку событий
            self.gui.newTodo.on('keypress', self.onKeyPressNewTodo);
            self.gui.clearCompleted.on('click', self.onClickClearCompleted);
            self.gui.toggleAll.on('change', self.onChangeToggleAll);
            self.gui.linkLogout.on('click', self.onClickLogout);

            $(window).on('hashchange', self.onHashChange); // обработка хэша

            self.initializeHash();
            self.render();

            self.authorization = new authorization(self);
            self.token = self.authorization.checkToken();
            self.loadItemsFromServer();
        };

        /**
         * Загружаем текущие знчаеяни с сервера
         */
        this.loadItemsFromServer = function () {
            self.server('GET', '/api/item', undefined, function (resData) {
                self.items = [];
                for (var i = 0, len = resData.data.length; i < len; i++) {
                    var row = resData.data[i];

                    var item = new ItemTodo(self);
                    item.id = row.id;
                    item.title = row.title;
                    item.completed = (row.completed === '1');

                    self.items.push(item);
                }
                self.render();
            });
        };

        /**
         * Анализ хэша (влияет на фильтр)
         */
        this.initializeHash = function () {
            var hash = window.location.hash.replace('#/', '');

            if (hash === '') {
                self.filterCompleted = undefined;
            } else if (hash === 'active') {
                self.filterCompleted = false;
            } else if (hash === 'completed') {
                self.filterCompleted = true;
            }

            $('.filters a', self.gui.el).removeClass();
            $('.filters a[href="#/' + hash + '"]', self.gui.el).addClass('selected');
        };

        /**
         * Изменение хэша
         */
        this.onHashChange = function () {
            self.initializeHash();
            self.render();
        };

        /**
         * Разлогинится
         * @param e
         */
        this.onClickLogout = function (e) {
            e.preventDefault();
            self.authorization.logout();
        };

        /**
         * Создание записи при нажатие на Enter
         * @param e
         */
        this.onKeyPressNewTodo = function (e) {
            if (e.which === 13 && self.gui.newTodo.val().trim()) {
                var item = self.newItem();
                item.saveDb();

                self.items.push(item);
                self.render();
                self.gui.newTodo.val(undefined);
            }
        };

        /**
         * Удалить строки помеченные как выполенные
         * @param e
         */
        this.onClickClearCompleted = function (e) {
            var update = false;
            for (var i = self.items.length - 1; i >= 0; i--) {
                if (self.items[i].completed) {
                    update = true;
                    self.items.splice(i, 1);
                }
            }

            self.render();
        };

        /**
         * Меняет для всех строк значение completed
         * @param e
         */
        this.onChangeToggleAll = function (e) {
            var value = self.gui.toggleAll.prop('checked');
            for (var i = 0, len = self.items.length; i < len; i++) {
                self.items[i].completed = value;
                self.items[i].saveDb();
            }

            self.render();
        };

        /**
         * Создать объект для новой строки
         * @returns {{}}
         */
        this.newItem = function () {
            var item = new ItemTodo(self);
            item.title = self.gui.newTodo.val().trim();
            item.completed = false;
            return item;
        };

        /**
         * рендерим одну строку из списка
         * @param item Объект типа ItemTodo
         */
        this.renderOneItem = function (item) {
            var html = self.templateItem(item);
            var newDom = $(html);

            item.dom.replaceWith(newDom);
            item.dom = newDom;

            item.initGUI();
        };

        /**
         * Удаление определенного пункт
         * @param item Объект типа ItemTodo
         */
        this.deleteOneItem = function (item) {
            for (var i = 0, len = self.items.length; i < len; i++) {
                if (self.items[i].id === item.id) {
                    self.items.splice(i, 1);
                    self.render();
                    return;
                }
            }
        };

        /**
         * Вывод списка
         * @param itemRender Если надо обновить только один пункт
         */
        this.render = function (itemRender) {
            var renderAll = (typeof itemRender === "undefined");

            if (renderAll) {
                self.gui.todoList.empty();
            } else {
                self.renderOneItem(itemRender);
            }

            var countCompleted = 0; // количество выполненных задач
            var countNotCompleted = 0; // количество НЕ выполненных задач

            for (var i = 0, len = self.items.length; i < len; i++) {
                var item = self.items[i];

                if (item.completed) {
                    countCompleted++;
                } else {
                    countNotCompleted++;
                }

                if (self.filterCompleted !== undefined) {
                    if (item.completed !== self.filterCompleted) {
                        continue;
                    }
                }

                if (renderAll) {
                    var html = this.templateItem(item);
                    var lastItem = self.gui.todoList.append(html).find('li:last');
                    item.setDOM(lastItem);
                }
            }

            self.gui.todoCount.text(countNotCompleted);

            // прячем\показываем кнопка "удалить выполненные"
            if (countCompleted === 0) {
                self.gui.clearCompleted.hide();
            } else {
                self.gui.clearCompleted.show();
            }

            // прячем\показываем нижний футер
            if (self.items.length === 0) {
                self.gui.sectionFooter.hide();
                self.gui.sectionMain.hide();
            } else {
                self.gui.sectionFooter.show();
                self.gui.sectionMain.show();
            }
        };

        /**
         * Проверяем значение на пустое
         * @param value
         * @returns {boolean}
         */
        this.empty = function (value) {
            return typeof value === 'undefined' || value === null || value === 0 ||
                (typeof value.length !== 'undefined' && value.length === 0)
        };

        /**
         * Запрос на сервер с передачей токена
         * @param method Меотод
         * @param url адрес
         * @param data Данные
         * @param successFun Обработчик удачного завершения
         * @param errorsFun Обработчик ошибки
         */
        this.server = function (method, url, data, successFun, errorsFun) {

            if (typeof successFun !== "function") {
                successFun = function (returnData, textStatus, jqXHR) {
                    debugger;
                }
            }

            if (typeof errorsFun !== "function") {
                errorsFun = function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                    $('body').append(method + ' ' + url + "<br>");
                    $('body').append(jqXHR.responseText);
                    debugger;
                }
            }

            var token = self.authorization.checkToken();
            if (self.empty(token)) {
                console.log('error authorization');
                debugger;
                return;
            }

            var resData = self.empty(data) ? undefined : JSON.stringify(data);

            $.ajax({
                method: method,
                url: url,
                headers: {
                    'X-authorization': token
                },
                dataType: 'json',
                data: resData,
                success: successFun,
                error: errorsFun
            })
        };

        this.initialize();
    };

    var app = new AppTodo();
})(window);
