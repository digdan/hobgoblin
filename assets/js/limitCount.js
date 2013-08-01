function limitCount(element,limit) {
        var obj = document.getElementById(element);
        if (limit == undefined) {
                limit = obj.maxLength;
        } else {
                obj.maxLength = limit;
        }

        obj.onkeypress = function() {
                if ( this.maxLength != -1) {
                        if (this.value.length > this.maxLength) this.value = this.value.substr(0,this.maxLength);
                        var counter = document.getElementById( this.getAttribute('data-counter'));
                        if ( counter != undefined ) counter.innerHTML = String(this.value.length) + ' / ' + String(this.maxLength);
                }
        }
        obj.onkeypress();
}