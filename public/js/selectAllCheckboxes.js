class SelectAllCheckboxes {
    constructor() {
        this.selectAll = document.getElementById("select-all");
        this.checkboxElts = document.querySelectorAll("table .checkbox");
        this.checked = true;
        this.init();
    }

    init() {
        this.selectAll.addEventListener("click", this.checkAll.bind(this));
    }

    checkAll() {
        if (this.selectAll.checked === true) {
            this.checked = true;
        } else {
            this.checked = false;
        }
        this.checkboxElts.forEach(function (checkbox) {
            checkbox.checked = this.checked;
        }.bind(this));
    }
}