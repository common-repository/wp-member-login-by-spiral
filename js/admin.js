window.addEventListener("load", function () {
	var params = new window.URLSearchParams(window.location.search);
	var tab = params.get('tab');

	var basicConfigs = document.getElementsByClassName("basic-config-label");
	var advanceConfigs = document.querySelectorAll("tr.advance-config")
	switch (tab) {
		case "advance-settings":
			hideBasicConfig();
			showAdvanceConfig()
			break;
		case null:
			hideAdvanceConfig()
			showBasicConfig()
			break;
	}

	function hideBasicConfig() {
		for (let index = 0; index < basicConfigs.length; index++) {
			const element = basicConfigs[index];
			element.classList.add("hidden");
			element.parentElement.parentElement.previousElementSibling.classList.add("hidden")
		}
	}

	function showBasicConfig() {
		for (let index = 0; index < basicConfigs.length; index++) {
			const element = basicConfigs[index];
			element.classList.remove("hidden");
			element.parentElement.parentElement.previousElementSibling.classList.remove("hidden")
		}
	}

	function hideAdvanceConfig() {
		for (let index = 0; index < advanceConfigs.length; index++) {
			const element = advanceConfigs[index];
			element.classList.add("hidden");
			element.parentElement.parentElement.previousElementSibling.classList.add("hidden")
		}
	}

	function showAdvanceConfig() {
		for (let index = 0; index < advanceConfigs.length; index++) {
			const element = advanceConfigs[index];
			element.classList.remove("hidden");
			element.parentElement.parentElement.previousElementSibling.classList.remove("hidden")
		}
	}
});
