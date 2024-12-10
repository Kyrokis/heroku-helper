var site = {};

(function () {
	
	/**
	 * Send message
	 */
	site.sendMessage = function () {
		$.get('/telegram/default/send-message', {
			'chat_id': $('#telegramform-chatid').val(),
			'text': $('#telegramform-text').val(),
		}).done(function (data) {
			console.log(data);
		});
		return false;
	};

	/**
	 * Get data
	 */
	site.getData = function () {
		var item = $(this);
		item.text('Загружаю...').addClass('disabled');
		$.get('/helper/default/get-data', {
			'link': $('#items-link').val(),
			'id_template': $('#items-id_template').val(),
			'offset': $('#items-offset').val(),
			'include': JSON.stringify($('#items-include').val()),
			'exclude': JSON.stringify($('#items-exclude').val()),
		}).done(function (data) {
			data = JSON.parse(data);
			console.log(data);
			$('#items-title').val(data.title);
			$('#items-link_img').val(data.link_img);
			$('#items-link_new').val(data.link_new);
			$('#items-now').val(data.now);
		}).always(function () {
			item.text('Загрузить').removeClass('disabled');
		});
		return false;
	};

	/**
	 * Let's helping
	 */
	site.helping = function () {
		$(this).text('Helping...').addClass('disabled');
		$.get('/helper/default/helping', {'user_id': $('.user-header').data('user_id')}).done(function (data) {
			location.reload();
		});
		return false;
	};

	/**
	 * Let's copying
	 */
	site.copy = function () {
		var item = $(this);
		var id = item.data('id');
		var module = '/' + item.data('module');
		$.get(module + '/default/copy', {'id': id}).done(function (data) {
			if (data) {
				location.reload();
			} else {
				console.log('Что-то пошло не так');
			}
		});
		return false;
	};

	/**
	 * Check selected title
	 */
	site.check = function () {
		var item = $(this);
		var id = item.data('id');
		$.get('/helper/default/check', {'id': id}).done(function (data) {
			if (data) {
				var titleNew = item.parent().prev().prev().children().html();
				item.parent().prev().prev().prev().html(titleNew);
				item.parent().parent().removeClass('info');
				item.next().remove();
				item.remove();
			} else {
				console.log('Что-то пошло не так');
			}
		});
		return false;
	};

	/**
	 * Check selected title
	 */
	site.checkHistory = function () {
		var item = $(this);
		var id = item.data('id');
		var value = item[0].checked;
		var type = item.data('type');
		console.log(id, value);
		$.get('/helper/default/check-history', {'id': id, 'value': value, 'type': type}).done(function (data) {
			if (data) {
				if (type != '') {
					if (data == '1') {
						if (type == 'first') {
							item.parent().next().children('input').remove();
						} else if (type == 'last') {
							item.parent().prev().children('input').remove();
						}
						item.parent().parent().removeClass('info');
						item.remove();
					} else {
						item.next().remove();
						item.parent().html(data);
					}
				}
				console.log('Все прошло так');
			} else {
				console.log('Что-то пошло не так');
			}
		});
		return false;
	};

	site.selectAll = function() {
		var include = $('#items-include').find('option');
		include.each(function() {
			$(this).val($(this).text());
		});
		include.prop('selected', true);

		var exclude = $('#items-exclude').find('option');
		exclude.each(function() {
			$(this).val($(this).text());
		});
		exclude.prop('selected', true);
		return true;
	};

	site.delWord = function() {
		var item = $(this).prev().prev().prev();
		var list = item.val();
		list.forEach(function(i) {
			item.find('option[value=\'' + i + '\']').remove();
		});
	};

	site.addWord = function() {
		var item = $(this).prev().prev();
		var text = $(this).prev().val();
		var option = $('<option>', {value: text, text : text});
		if (text != '') {
			item.append(option);
			$(this).prev().val('');
		}
		console.log(text);
	};

})();

$(function () {
	$('body').on('click', '.send-msg', site.sendMessage);
	$('body').on('click', '.helping', site.helping);
	$('body').on('click', '.check', site.check);
	$('body').on('change', '.checkHistory', site.checkHistory);
	$('body').on('click', '.copy', site.copy);
	$('body').on('click', '.del-word', site.delWord);
	$('body').on('click', '.add-word', site.addWord);
	$('body').on('submit', '.items-add', site.selectAll);
	$('body').on('click', '.get-data', site.selectAll);
	$('body').on('click', '.get-data', site.getData);
});