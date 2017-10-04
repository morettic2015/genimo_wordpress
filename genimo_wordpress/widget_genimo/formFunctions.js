/*
 * @Copyright https://experienciasdigitais.com.br 
 */

var GenimoForm = function () {
    this.isValid = true;
    this.msgError = null;

    this.isEmailValid = function (field, msg) {
        var regularExpression = '^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$';
        emailAddress = field.value;
        this.isValid = regularExpression.test(emailAddress);
        if (!this.isValid) {
            field.style.borderColor = "red";
            field.style.border = "thick solid";
            field.focus();
        }
    }
    this.validateField = function (field, msg) {
        var element = document.getElementById(field);
        if (element.value === "") {
            this.isValid = false;
            this.msgError += msg;
            element.style.borderColor = "red";
            element.style.border = "thick solid";
        } else {
            element.style.borderColor = "green";
            element.style.border = "thick solid";
        }
        // alert(element.value);
    }
    this.initEvents = function () {
        document.getElementById('dsemail').onblur = function () {
            controlValidation.isEmailValid(this, 'Formato do email é inválido');
        }
        document.getElementById("btsubmitimovel").onclick = function () {
            controlValidation.validateAll(
                    'Nome do Proprietário é obrigatório',
                    'Email de contato é obrigatório',
                    'Número do Whatsapp é obrigatório!',
                    'Informe o tipo do negócio!',
                    'Informe o tipo do imóvel!',
                    'Informe o numero de quartos!',
                    'Informe o numero de banheiros!',
                    'Qual a localização do imóvel?',
                    'Conte a história do seu imóvel'
                    );
        };
        document.getElementById('nuphone').onblur = function () {
            mask(this, mphone);
        }
        document.getElementById('vlprice').onblur = function () {
            mask(this, formatReal);
        }
        initAutocomplete();
    }

    this.validateAll = function (a, b, c, d, e, f, g, h, i) {
        this.validateField('nmperson', a);
        this.validateField('dsemail', b);
        this.validateField('nuphone', c);
        this.validateField('cdmode', d);
        this.validateField('idcategory', e);
        this.validateField('nrquartos', f);
        this.validateField('nrbath', g);
        this.validateField('deaddress', h);
        this.validateField('deimovel', i);

        if (!this.isValid) {
            //ons.notification.alert(this.msgError);
        } else {
            document.getElementById('rendered-form').submit();
        }
        this.isValid = true;
    }
}
var controlValidation = new GenimoForm();
controlValidation.initEvents();
//Form submit validation



/**
 * @Utility functions
 * */

function mask(o, f) {
    setTimeout(function () {
        var v = f(o.value);
        if (v != o.value) {
            o.value = v;
        }
    }, 1);
}

function mphone(v) {
    var r = v.replace(/\D/g, "");
    r = r.replace(/^0/, "");
    if (r.length > 10) {
        // 11+ digits. Format as 5+4.
        r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "(055$1) $2-$3");
    } else if (r.length > 5) {
        // 6..10 digits. Format as 4+4
        r = r.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "(055$1) $2-$3");
    } else if (r.length > 2) {
        // 3..5 digits. Add (0XX..)
        r = r.replace(/^(\d\d)(\d{0,5})/, "(055$1) $2");
    } else {
        // 0..2 digits. Just add (0XX
        r = r.replace(/^(\d*)/, "(055$1");
    }
    return r;
}
function formatReal(v) {
    var t1 = parseInt(v.replace(/[\D]+/g, ''))
    var tmp = t1 + '';
    tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
    if (tmp.length > 6)
        tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    return tmp;
}


/**
 * @Places API
 * */

var placeSearch, autocomplete;
var componentForm = {
    /*street_number: 'short_name',
     route: 'long_name',
     locality: 'long_name',
     administrative_area_level_1: 'short_name',
     country: 'long_name',
     postal_code: 'short_name'*/
};

function initAutocomplete() {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('deaddress')),
            {types: ['geocode']});

    // When the user selects an address from the dropdown, populate the address
    // fields in the form.
    autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete.getPlace();

    document.getElementById('vllat').value = ((place.geometry.viewport.f.f + place.geometry.viewport.f.b) / 2);
    document.getElementById('vllon').value = ((place.geometry.viewport.b.f + place.geometry.viewport.b.b) / 2);
    document.getElementById('nmaddress').value = place.formatted_address;
    document.getElementById('nmbairro').value = place.vicinity;
    document.getElementById('nmstate').value = place.address_components[3].short_name;
    document.getElementById('nmcountry').value = place.address_components[4].long_name;
    document.getElementById('nrzip').value = place.address_components[5].long_name;

    /*   for (var component in componentForm) {
     document.getElementById(component).value = '';
     document.getElementById(component).disabled = false;
     }
     
     // Get each component of the address from the place details
     // and fill the corresponding field on the form.
     for (var i = 0; i < place.address_components.length; i++) {
     var addressType = place.address_components[i].types[0];
     if (componentForm[addressType]) {
     var val = place.address_components[i][componentForm[addressType]];
     document.getElementById(addressType).value = val;
     }
     }*/
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
        });
    }
}