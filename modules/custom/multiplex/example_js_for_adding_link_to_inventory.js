// Assign a link to an inventory item, ensuring that the inventory box has initialized, otherwise defering the action.
function assignLinkToInventory(item_id, url) {
	if (document.readyState === 'complete') {
		if (typeof InventoryBox != "undefined") {
			InventoryBox.setItemLink(item_id, url);
		}
	}
	else {
		window.addEventListener("load", () => {
			assignLinkToInventory(item_id, url);
		});
	}
}
assignLinkToInventory("item_id", "link_path");