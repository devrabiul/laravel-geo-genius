const itiInstances = new Map();

const mobileExamples = {
    af: "+93700123456",      // Afghanistan
    al: "+355691234567",     // Albania
    dz: "+213661234567",     // Algeria
    as: "+16842501234",      // American Samoa
    ad: "+376312345",        // Andorra
    ao: "+244923123456",     // Angola
    ai: "+12641234567",      // Anguilla
    ag: "+12681234567",      // Antigua and Barbuda
    ar: "+5491123456789",    // Argentina
    am: "+37477123456",      // Armenia
    aw: "+2971234567",       // Aruba
    au: "+61412345678",      // Australia
    at: "+436601234567",     // Austria
    az: "+994501234567",     // Azerbaijan
    bs: "+12421234567",      // Bahamas
    bh: "+97336001234",      // Bahrain
    bd: "+8801712345678",    // Bangladesh
    bb: "+12462345678",      // Barbados
    by: "+375291234567",     // Belarus
    be: "+32475123456",      // Belgium
    bz: "+5016123456",       // Belize
    bj: "+22991234567",      // Benin
    bm: "+14412345678",      // Bermuda
    bt: "+97517123456",      // Bhutan
    bo: "+59171234567",      // Bolivia
    ba: "+38761234567",      // Bosnia and Herzegovina
    bw: "+26771234567",      // Botswana
    br: "+5511912345678",    // Brazil
    io: "+2461234567",       // British Indian Ocean Territory
    bn: "+6737123456",       // Brunei Darussalam
    bg: "+359881234567",     // Bulgaria
    bf: "+22670123456",      // Burkina Faso
    bi: "+25771234567",      // Burundi
    kh: "+85512345678",      // Cambodia
    cm: "+237650123456",     // Cameroon
    ca: "+14161234567",      // Canada
    cv: "+2389912345",       // Cape Verde
    ky: "+13451234567",      // Cayman Islands
    cf: "+23675012345",      // Central African Republic
    td: "+23565012345",      // Chad
    cl: "+56912345678",      // Chile
    cn: "+8613712345678",    // China
    cx: "+61891620000",      // Christmas Island
    cc: "+61891620000",      // Cocos (Keeling) Islands
    co: "+573001234567",     // Colombia
    km: "+2693212345",       // Comoros
    cd: "+243810123456",     // Congo (DRC)
    cg: "+2420551234567",    // Congo
    ck: "+68212345",         // Cook Islands
    cr: "+50683123456",      // Costa Rica
    ci: "+2250701234567",    // Côte d'Ivoire
    hr: "+385912345678",     // Croatia
    cu: "+5351234567",       // Cuba
    cw: "+59991234567",      // Curaçao
    cy: "+35799123456",      // Cyprus
    cz: "+420601234567",     // Czech Republic
    dk: "+4520123456",       // Denmark
    dj: "+25377123456",      // Djibouti
    dm: "+17671234567",      // Dominica
    do: "+18091234567",      // Dominican Republic
    ec: "+593912345678",     // Ecuador
    eg: "+201001234567",     // Egypt
    sv: "+50370123456",      // El Salvador
    gq: "+240222123456",     // Equatorial Guinea
    er: "+291712345",        // Eritrea
    ee: "+37251234567",      // Estonia
    et: "+251911234567",     // Ethiopia
    fk: "+50012345",         // Falkland Islands
    fo: "+298321234",        // Faroe Islands
    fj: "+6797123456",       // Fiji
    fi: "+358401234567",     // Finland
    fr: "+33612345678",      // France
    gf: "+594694123456",     // French Guiana
    pf: "+68987123456",      // French Polynesia
    ga: "+24107123456",      // Gabon
    gm: "+2207123456",       // Gambia
    ge: "+995551234567",     // Georgia
    de: "+4915123456789",    // Germany
    gh: "+233241234567",     // Ghana
    gi: "+35020012345",      // Gibraltar
    gr: "+306912345678",     // Greece
    gl: "+299321234",        // Greenland
    gd: "+14732345678",      // Grenada
    gp: "+590690123456",     // Guadeloupe
    gu: "+16714812345",      // Guam
    gt: "+50241234567",      // Guatemala
    gg: "+441481123456",     // Guernsey
    gn: "+224621234567",     // Guinea
    gw: "+24599123456",      // Guinea-Bissau
    gy: "+5926001234",       // Guyana
    ht: "+50937123456",      // Haiti
    hn: "+50499123456",      // Honduras
    hk: "+85291234567",      // Hong Kong
    hu: "+36701234567",      // Hungary
    is: "+3546612345",       // Iceland
    in: "+919000000000",     // India
    id: "+628123456789",     // Indonesia
    ir: "+989121234567",     // Iran
    iq: "+9647712345678",    // Iraq
    ie: "+353851234567",     // Ireland
    im: "+441624123456",     // Isle of Man
    il: "+972501234567",     // Israel
    it: "+393401234567",     // Italy
    jm: "+18761234567",      // Jamaica
    jp: "+819012345678",     // Japan
    je: "+441534123456",     // Jersey
    jo: "+962791234567",     // Jordan
    kz: "+77712345678",      // Kazakhstan
    ke: "+254712345678",     // Kenya
    ki: "+68612345",         // Kiribati
    kw: "+96551234567",      // Kuwait
    kg: "+996701234567",     // Kyrgyzstan
    la: "+8562012345678",    // Laos
    lv: "+37120123456",      // Latvia
    lb: "+96170123456",      // Lebanon
    ls: "+26651234567",      // Lesotho
    lr: "+231770123456",     // Liberia
    ly: "+218912345678",     // Libya
    li: "+4231234567",       // Liechtenstein
    lt: "+37061234567",      // Lithuania
    lu: "+352621234567",     // Luxembourg
    mo: "+85361234567",      // Macau
    mk: "+38970123456",      // North Macedonia
    mg: "+261320123456",     // Madagascar
    mw: "+265991234567",     // Malawi
    my: "+60123456789",      // Malaysia
    mv: "+9607123456",       // Maldives
    ml: "+22370123456",      // Mali
    mt: "+35699123456",      // Malta
    mh: "+6924912345",       // Marshall Islands
    mq: "+596696123456",     // Martinique
    mr: "+22240123456",      // Mauritania
    mu: "+23057212345",      // Mauritius
    yt: "+262639012345",     // Mayotte
    mx: "+5215512345678",    // Mexico
    fm: "+6913201234",       // Micronesia
    md: "+37369123456",      // Moldova
    mc: "+37761234567",      // Monaco
    mn: "+97699123456",      // Mongolia
    me: "+38260123456",      // Montenegro
    ms: "+16642345678",      // Montserrat
    ma: "+212612345678",     // Morocco
    mz: "+258841234567",     // Mozambique
    mm: "+959251234567",     // Myanmar
    na: "+264811234567",     // Namibia
    nr: "+6745551234",       // Nauru
    np: "+9779812345678",    // Nepal
    nl: "+31612345678",      // Netherlands
    nc: "+687123456",        // New Caledonia
    nz: "+64211234567",      // New Zealand
    ni: "+50585123456",      // Nicaragua
    ne: "+22790123456",      // Niger
    ng: "+2348012345678",    // Nigeria
    nu: "+6831234",          // Niue
    nf: "+67231234",         // Norfolk Island
    kp: "+8501912345678",    // North Korea
    mp: "+16704812345",      // Northern Mariana Islands
    no: "+4791234567",       // Norway
    om: "+96891234567",      // Oman
    pk: "+923001234567",     // Pakistan
    pw: "+6805551234",       // Palau
    ps: "+970599123456",     // Palestine
    pa: "+5076012345",       // Panama
    pg: "+67572123456",      // Papua New Guinea
    py: "+595981234567",     // Paraguay
    pe: "+51981234567",      // Peru
    ph: "+639171234567",     // Philippines
    pl: "+48500123456",      // Poland
    pt: "+351912345678",     // Portugal
    pr: "+17871234567",      // Puerto Rico
    qa: "+97450123456",      // Qatar
    re: "+262692123456",     // Réunion
    ro: "+40712345678",      // Romania
    ru: "+79261234567",      // Russia
    rw: "+250788123456",     // Rwanda
    bl: "+590690123456",     // Saint Barthélemy
    sh: "+29012345",         // Saint Helena
    kn: "+18692234567",      // Saint Kitts and Nevis
    lc: "+17582345678",      // Saint Lucia
    mf: "+590690123456",     // Saint Martin
    pm: "+508123456",        // Saint Pierre and Miquelon
    vc: "+17841234567",      // Saint Vincent and the Grenadines
    ws: "+6857012345",       // Samoa
    sm: "+37866123456",      // San Marino
    st: "+2399912345",       // São Tomé and Príncipe
    sa: "+966501234567",     // Saudi Arabia
    sn: "+221701234567",     // Senegal
    rs: "+381601234567",     // Serbia
    sc: "+2482512345",       // Seychelles
    sl: "+23299123456",      // Sierra Leone
    sg: "+6581234567",       // Singapore
    sx: "+17219876543",      // Sint Maarten
    sk: "+421901234567",     // Slovakia
    si: "+38640123456",      // Slovenia
    sb: "+6777123456",       // Solomon Islands
    so: "+252612345678",     // Somalia
    za: "+27821234567",      // South Africa
    kr: "+821012345678",     // South Korea
    ss: "+211912345678",     // South Sudan
    es: "+34600123456",      // Spain
    lk: "+94771234567",      // Sri Lanka
    sd: "+249912345678",     // Sudan
    sr: "+5979123456",       // Suriname
    sz: "+26876123456",      // Eswatini (Swaziland)
    se: "+46701234567",      // Sweden
    ch: "+41791234567",      // Switzerland
    sy: "+963912345678",     // Syria
    tw: "+886912345678",     // Taiwan
    tj: "+992901234567",     // Tajikistan
    tz: "+255712345678",     // Tanzania
    th: "+66812345678",      // Thailand
    tl: "+67077234567",      // Timor-Leste
    tg: "+22890123456",      // Togo
    tk: "+6901234",          // Tokelau
    to: "+6767123456",       // Tonga
    tt: "+18681234567",      // Trinidad and Tobago
    tn: "+21620123456",      // Tunisia
    tr: "+905301234567",     // Turkey
    tm: "+99361234567",      // Turkmenistan
    tc: "+16492123456",      // Turks and Caicos Islands
    tv: "+6889123456",       // Tuvalu
    ug: "+256701234567",     // Uganda
    ua: "+380501234567",     // Ukraine
    ae: "+971501234567",     // United Arab Emirates
    gb: "+447912345678",     // United Kingdom
    us: "+12025550123",      // United States
    uy: "+59891234567",      // Uruguay
    uz: "+998901234567",     // Uzbekistan
    vu: "+6785012345",       // Vanuatu
    va: "+37912345678",      // Vatican City
    ve: "+584121234567",     // Venezuela
    vn: "+84981234567",      // Vietnam
    wf: "+681123456",       // Wallis and Futuna
    ye: "+967712345678",     // Yemen
    zm: "+260971234567",     // Zambia
    zw: "+263712345678"      // Zimbabwe
};

function initializeIntlTelInput() {
    const inputs = document.querySelectorAll(
        'input[type="tel"]:not([data-intl-initialized])'
    );

    const defaultCountryConfigEl = document.querySelector(".system-default-country-code");

    // dataset converts all `data-xxx` into camelCase automatically:
    const configDataset = defaultCountryConfigEl?.dataset || {};

    function toBool(value) {
        return value === "1" || value === "true";
    }

    // Parse `onlyCountriesArray` safely from string (e.g. "['us','gb']")
    function parseCountries(value) {
        if (!value || value === "[]" || value === "") return [];
        try {
            // Try to parse JSON-style or comma-separated strings
            if (value.startsWith("[")) {
                return JSON.parse(value.replace(/'/g, '"'));
            }
            return value.split(",").map(c => c.trim().toLowerCase());
        } catch {
            return [];
        }
    }

    const separateDialCode = configDataset.separateDialCode !== undefined
        ? toBool(configDataset.separateDialCode)
        : true; // default true

    const showSelectedDialCode = configDataset.showSelectedDialCode !== undefined
        ? toBool(configDataset.showSelectedDialCode)
        : false; // default false

    const autoInsertDialCode = configDataset.autoInsertDialCode !== undefined
        ? toBool(configDataset.autoInsertDialCode)
        : false; // default false

    const nationalMode = configDataset.nationalMode !== undefined
        ? toBool(configDataset.nationalMode)
        : false; // default false

    const onlyCountries = parseCountries(configDataset.onlyCountriesArray);
    const onlyCountriesMode = toBool(configDataset.onlyCountriesMode);

    // choose detected_country first, fallback to initial_country, then "us"
    const defaultCountry =
        (configDataset.detectedCountry || configDataset.initialCountry || "us").toLowerCase();

    inputs.forEach(input => {
        const iti = window.intlTelInput(input, {
            initialCountry: defaultCountry,
            autoInsertDialCode: autoInsertDialCode,
            nationalMode: nationalMode,
            formatOnDisplay: false,
            separateDialCode: separateDialCode,
            showSelectedDialCode: showSelectedDialCode,
            autoPlaceholder: configDataset.autoPlaceholder || "off",
            // Add onlyCountries only if mode is enabled and array is not empty
            ...(onlyCountriesMode && onlyCountries.length > 0
            ? { onlyCountries: onlyCountries }
            : {}),
            utilsScript:
                "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.15/build/js/utils.js"
        });

        // Override _setFlag after init
        const originalSetFlag = iti._setFlag;
        iti._setFlag = function(countryCode) {
            const result = originalSetFlag.call(this, countryCode);

            if (this.options.showSelectedDialCode && this.isRTL) {
                const selectedFlagWidth =
                    this.selectedFlag.offsetWidth ||
                    this._getHiddenSelectedFlagWidth();
                this.telInput.style.paddingLeft = `${selectedFlagWidth + 6}px`;
                this.telInput.style.paddingRight = "";
            }

            return result;
        };

        iti._setFlag(iti.getSelectedCountryData().iso2);

        // itiInstances.set(input, iti);
        // input.setAttribute("data-intl-initialized", "true");

        // ✅ Move name & value to a hidden input
        const originalName = input.getAttribute('name');
        const originalValue = input.value;
        input.removeAttribute('name'); // remove name from visible input

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = originalName;
        input.parentNode.appendChild(hiddenInput);

        try {
            const originalSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            const originalGetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').get;

            Object.defineProperty(hiddenInput, 'value', {
                set(newValue) {
                    originalSetter.call(this, newValue);
                    iti.setNumber(newValue); // uses intlTelInput to format & update country
                },
                get() {
                    return originalGetter.call(this);
                }
            });
        } catch (e) {
        }

        const hiddenCountryCodeInput = document.createElement('input');
        hiddenCountryCodeInput.type = 'hidden';
        hiddenCountryCodeInput.name = originalName + '_country_code';
        input.parentNode.appendChild(hiddenCountryCodeInput);

        // ✅ Set initial value if exists
        if (originalValue) {
            iti.setNumber(originalValue);
            hiddenInput.value = iti.getNumber(); // formatted full number
            hiddenCountryCodeInput.value = iti?.getSelectedCountryData()?.dialCode;

            // Set data-value and data-country
            input.setAttribute('data-value', iti.getNumber());
            input.setAttribute('data-country', iti.getSelectedCountryData()?.iso2);
        }

        // ✅ Keep updating hidden input on user change
        input.addEventListener('input', () => {
            hiddenInput.value = iti.getNumber();
            hiddenCountryCodeInput.value = iti?.getSelectedCountryData()?.dialCode;
        });

        input.addEventListener('countrychange', () => {
            hiddenInput.value = iti.getNumber();
            hiddenCountryCodeInput.value = iti?.getSelectedCountryData()?.dialCode;
        });

        iti._setFlag(iti.getSelectedCountryData().iso2);
        itiInstances.set(input, iti);
        input.setAttribute("data-intl-initialized", "true");
    });
}

function keepOnlyNumbers(str) {
    return str.replace(/\D/g, "");
}

function getLocalExampleNumberLength(iti) {
    try {
        const iso2 = iti.getSelectedCountryData().iso2;
        let dialCode = iti.getSelectedCountryData().dialCode;

        if (!iso2 || !dialCode) {
            console.warn("No country or dial code found");
            return 12; // fallback
        }

        dialCode = dialCode.toString();

        // Use example from your predefined list or intl-tel-input utils fallback
        let example = mobileExamples[iso2];
        if (!example && window.intlTelInputUtils) {
            example = intlTelInputUtils.getExampleNumber(
                iso2,
                true,
                intlTelInputUtils.numberFormat.E164
            );
        }

        if (!example) {
            console.warn("No example number found for", iso2);
            return 12; // fallback
        }

        const digitsOnly = example.replace(/\D/g, "");

        // Check if dialCode is at start of digitsOnly
        const dialCodeIndex = digitsOnly.startsWith(dialCode) ? 0 : -1;

        const nationalDigits =
            dialCodeIndex === 0
                ? digitsOnly.slice(dialCode.length)
                : digitsOnly; // fallback to full if dialCode not found at start

        return nationalDigits.length;
    } catch (e) {
        console.warn("Fallback to default local max length (12)", e);
        return 12;
    }
}

function validatePhoneInput(input) {
    const iti = itiInstances.get(input);
    if (!iti || typeof iti.isValidNumber !== "function") return false;

    const fullNumber = iti.getNumber(); // E164 format
    const dialCode = iti.getSelectedCountryData().dialCode;
    const digitsOnly = keepOnlyNumbers(fullNumber);
    const dialCodeIndex = digitsOnly.indexOf(dialCode);
    const localNumber =
        dialCodeIndex >= 0
            ? digitsOnly.slice(dialCodeIndex + dialCode.length)
            : digitsOnly;

    const expectedLength = getLocalExampleNumberLength(iti);
    const isValid = iti.isValidNumber() && localNumber.length === expectedLength;

    input.classList.toggle("is-valid", isValid);
    input.classList.toggle("is-invalid", !isValid);

    return isValid;
}

document.addEventListener("DOMContentLoaded", () => {
    initializeIntlTelInput();

    document.addEventListener("input", function (e) {
        if (e.target.matches('input[type="tel"]')) {
            const input = e.target;
            const iti = itiInstances.get(input);
            if (!iti) return;

            const inputVal = keepOnlyNumbers(input.value);
            const dialCode = iti.getSelectedCountryData().dialCode;
            const expectedLength = getLocalExampleNumberLength(iti);

            // Extract local part (remove dial code if present)
            const dialCodeIndex = inputVal.indexOf(dialCode);
            let localPart =
                dialCodeIndex >= 0
                    ? inputVal.slice(dialCodeIndex + dialCode.length)
                    : inputVal;

            // Limit only if too long
            if (localPart.length > expectedLength) {
                localPart = localPart.slice(0, expectedLength + 4);
                input.value = localPart; // update field only when needed
            }

            // Update hidden input fields
            const hiddenInput = input.parentNode.querySelector('input[type="hidden"]:not([name="country_code"])');
            if (hiddenInput && iti) {
                hiddenInput.value = iti.getNumber();
            }

            const hiddenCountryCodeInput = input.parentNode.querySelector('input[name="country_code"]');
            if (hiddenCountryCodeInput && iti) {
                hiddenCountryCodeInput.value = iti.getSelectedCountryData()?.dialCode || "";
            }

            validatePhoneInput(input);
        }
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('reset', function () {
            setTimeout(() => {
                const phoneInputs = form.querySelectorAll('input[type="tel"]');

                phoneInputs.forEach(input => {
                    const iti = itiInstances.get(input);
                    if (!iti) return;

                    // Read from data-* attributes
                    const dataValue = input.getAttribute('data-value') || "";
                    const dataCountry = input.getAttribute('data-country') || "bd";

                    if (dataValue) {
                        iti.setCountry(dataCountry);       // set country first
                        iti.setNumber(dataValue);          // set full number
                        input.value = iti.getNumber();     // update visible input
                    } else {
                        iti.setCountry(dataCountry);
                        iti.setNumber("");
                        input.value = "";
                    }

                    // Update hidden input
                    const hiddenInput = input.parentNode.querySelector('input[type="hidden"]:not([name="country_code"])');
                    if (hiddenInput) hiddenInput.value = iti.getNumber();

                    // Update hidden country code input
                    const hiddenCountryCodeInput = input.parentNode.querySelector('input[name="country_code"]');
                    if (hiddenCountryCodeInput) hiddenCountryCodeInput.value = iti.getSelectedCountryData()?.dialCode || "";

                    // Re-set data-* attributes from current input state
                    input.setAttribute('data-value', iti.getNumber());
                    input.setAttribute('data-country', iti.getSelectedCountryData()?.iso2 || "");

                    input.classList.remove("is-valid", "is-invalid");
                });
            }, 0); // Wait for native reset to happen
        });
    });
});


try {
    // observer to watch for added inputs
    const observer = new MutationObserver(function(mutationsList) {
        mutationsList.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // element
                    if (node.matches('input[type="tel"]')) {
                        initIntlTelInputOn(node);
                    } else {
                        // also check descendants
                        node.querySelectorAll && node.querySelectorAll('input[type="tel"]').forEach(initIntlTelInputOn);
                    }
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    function initIntlTelInputOn(input) {
        if (!input.hasAttribute('data-intl-initialized')) {
            initializeIntlTelInput();
        }
    }

} catch (e) {

}
