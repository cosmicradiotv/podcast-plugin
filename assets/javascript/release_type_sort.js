$(function () {
	$('#sortable').sortable();/*.bind('sortupdate', function() {
		var data = {item_ids: [], item_orders: []};
		$(this).children('li').each(function(index, obj) {
			data.item_ids[index] = $(obj).data('release-type-id');
			data.item_orders[index] = index;
		});
		$('#sortForm').data('request-data',data);
	});*/

	$('#saveOrder').on("click", function(event) {
		var data = {redirect:0};
		data.item_ids = [];
		data.item_orders = [];

		$('#sortable > li').each(function(index, obj) {
			data.item_ids[index] = $(obj).data('release-type-id');
			data.item_orders[index] = index;
		});

		$(this).request('onSaveOrder', {
			data: data
		});

		event.preventDefault();
	});

	$('#saveOrderAndClose').on("click", function(event) {
		var data = {close:1};
		data.item_ids = [];
		data.item_orders = [];

		$('#sortable > li').each(function(index, obj) {
			data.item_ids[index] = $(obj).data('release-type-id');
			data.item_orders[index] = index;
		});

		$(this).request('onSaveOrder', {
			data: data
		});

		event.preventDefault();
	});
});