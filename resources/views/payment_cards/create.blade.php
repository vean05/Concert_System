@extends('layouts.app')

@section('title', 'Add Payment Card - ConcertHub')

@section('content')
<style>
    .page-container { padding: 2rem 0; max-width: 800px; margin: 0 auto; }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #5BA3C0;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .back-link:hover { transform: translateX(-5px); color: #4A8FA3; }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 2rem;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(31,38,135,0.08);
    }

    .form-group { margin-bottom: 1.5rem; position: relative; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50; }
    
    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .form-control:focus { outline: none; border-color: #5BA3C0; box-shadow: 0 0 0 3px rgba(91,163,192,0.1); }
    
    .form-control.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    .invalid-feedback { color: #dc3545; font-size: 0.85rem; margin-top: 0.3rem; display: none; }
    .is-invalid ~ .invalid-feedback { display: block; }

    /* Card Type Selector */
    .card-type-selector { display: flex; gap: 1rem; }
    .card-type-option {
        flex: 1;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    .card-type-option input { position: absolute; opacity: 0; cursor: pointer; }
    .card-type-option i { font-size: 2.5rem; margin-bottom: 0.5rem; display: block; }
    .card-type-option .fa-cc-visa { color: #1a1f71; }
    .card-type-option .fa-cc-mastercard { color: #eb001b; }
    .card-type-option span { font-weight: 600; color: #4a5568; }
    
    .card-type-option.selected { border-color: #5BA3C0; background: rgba(91,163,192,0.05); }

    /* Country Picker Custom UI */
    .country-selector { position: relative; }
    .country-display {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        background: white;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .country-display.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    .country-dropdown-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    .country-dropdown-modal {
        background: white;
        width: 90%;
        max-width: 500px;
        height: 80vh;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .country-modal-header {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .country-modal-header h3 { margin: 0; font-size: 1.2rem; }
    .btn-close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
    
    .country-modal-body {
        display: flex;
        flex: 1;
        overflow: hidden;
    }
    .country-list-container {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        scroll-behavior: smooth;
    }
    .alphabet-index {
        width: 30px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-evenly;
        font-size: 0.75rem;
        font-weight: 600;
        color: #5BA3C0;
        border-left: 1px solid #e0e0e0;
        padding: 0.5rem 0;
    }
    .alphabet-index span { cursor: pointer; width: 100%; text-align: center; }
    .alphabet-index span:hover { background: #e9ecef; }
    
    .letter-group-title {
        background: #f0f0f0;
        padding: 0.2rem 0.5rem;
        font-weight: 700;
        color: #666;
        margin-top: 1rem;
        border-radius: 4px;
    }
    .letter-group-title:first-child { margin-top: 0; }
    .country-item {
        padding: 0.8rem 0.5rem;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .country-item:hover { background: #f8f9fa; color: #5BA3C0; }

    .btn-save {
        background: linear-gradient(135deg, #5BA3C0 0%, #4A8FA3 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        width: 100%;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91,163,192,0.3); }

    .row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
    .col-half { width: 50%; padding: 0 10px; }
</style>

<div class="page-container">
    <a href="{{ route('payment_cards.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Cards
    </a>

    <div class="page-header">
        <h1><i class="fas fa-plus-circle" style="color:#5BA3C0;"></i> Add Payment Card</h1>
    </div>

    <div class="form-card">
        <form id="paymentCardForm" action="{{ route('payment_cards.store') }}" method="POST">
            @csrf

            <!-- Card Type -->
            <div class="form-group">
                <label>Card Type <span style="color:red;">*</span></label>
                <div class="card-type-selector" id="cardTypeSelector">
                    <label class="card-type-option" id="opt-visa">
                        <input type="radio" name="card_type" value="visa" class="d-none">
                        <i class="fab fa-cc-visa"></i>
                        <span>Visa</span>
                    </label>
                    <label class="card-type-option" id="opt-master">
                        <input type="radio" name="card_type" value="master" class="d-none">
                        <i class="fab fa-cc-mastercard"></i>
                        <span>Mastercard</span>
                    </label>
                </div>
                <div class="invalid-feedback" id="err-card_type">Please select a card type.</div>
            </div>

            <!-- Card Number -->
            <div class="form-group">
                <label for="card_number">Card Number <span style="color:red;">*</span></label>
                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="e.g. 4111222233334444" maxlength="19">
                <div class="invalid-feedback">Please enter a valid card number (digits only).</div>
            </div>

            <div class="row">
                <!-- Expiry Date -->
                <div class="form-group col-half">
                    <label for="expiry_date">Expiry Date (MM/YY) <span style="color:red;">*</span></label>
                    <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5">
                    <div class="invalid-feedback">Please enter expiry date in MM/YY format.</div>
                </div>

                <!-- CVV -->
                <div class="form-group col-half">
                    <label for="cvv">CVV <span style="color:red;">*</span></label>
                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" maxlength="4">
                    <div class="invalid-feedback">Please enter a valid CVV (digits only).</div>
                </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">Full Name as on card <span style="color:red;">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe">
                <div class="invalid-feedback">Please enter the cardholder's full name.</div>
            </div>

            <!-- Country -->
            <div class="form-group country-selector">
                <label>Country <span style="color:red;">*</span></label>
                <input type="hidden" name="country" id="country_input">
                <div class="country-display" id="country_display" onclick="openCountryModal()">
                    <span id="country_text" style="color: #6c757d;">Select a country...</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="invalid-feedback" id="err-country">Please select a country.</div>
            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="address">Billing Address <span style="color:red;">*</span></label>
                <textarea id="address" name="address" class="form-control" rows="3" placeholder="Enter your full billing address"></textarea>
                <div class="invalid-feedback">Please enter your billing address.</div>
            </div>

            <button type="button" class="btn-save" onclick="validateAndSubmit()">
                <i class="fas fa-save"></i> Save Card
            </button>
        </form>
    </div>
</div>

<!-- Country Dropdown Modal -->
<div class="country-dropdown-overlay" id="countryModal" onclick="closeCountryModal(event)">
    <div class="country-dropdown-modal" onclick="event.stopPropagation()">
        <div class="country-modal-header">
            <h3>Select Country</h3>
            <button class="btn-close-modal" onclick="closeCountryModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="country-modal-body">
            <div class="country-list-container" id="countryList">
                <!-- Populated by JS -->
            </div>
            <div class="alphabet-index" id="alphabetIndex">
                <!-- A to Z populated by JS -->
            </div>
        </div>
    </div>
</div>

<script>
    // Country List Data
    const countries = [
        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan",
        "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi",
        "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia",
        "Denmark", "Djibouti", "Dominica", "Dominican Republic",
        "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia",
        "Fiji", "Finland", "France",
        "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
        "Haiti", "Honduras", "Hungary",
        "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy",
        "Jamaica", "Japan", "Jordan",
        "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan",
        "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
        "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar",
        "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway",
        "Oman",
        "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
        "Qatar",
        "Romania", "Russia", "Rwanda",
        "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
        "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu",
        "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan",
        "Vanuatu", "Vatican City", "Venezuela", "Vietnam",
        "Yemen",
        "Zambia", "Zimbabwe"
    ];

    // Initialize Country Modal
    function initCountryPicker() {
        const countryList = document.getElementById('countryList');
        const alphabetIndex = document.getElementById('alphabetIndex');
        
        // Group by letter
        const grouped = {};
        countries.forEach(c => {
            const firstLetter = c.charAt(0).toUpperCase();
            if (!grouped[firstLetter]) grouped[firstLetter] = [];
            grouped[firstLetter].push(c);
        });

        // Build list HTML
        let listHtml = '';
        let alphabetHtml = '';

        for (let i = 65; i <= 90; i++) {
            const letter = String.fromCharCode(i);
            
            // Index letter
            alphabetHtml += `<span onclick="scrollToLetter('${letter}')">${letter}</span>`;

            // Group content
            if (grouped[letter]) {
                listHtml += `<div class="letter-group-title" id="group-${letter}">${letter}</div>`;
                grouped[letter].forEach(country => {
                    listHtml += `<div class="country-item" onclick="selectCountry('${country}')">${country}</div>`;
                });
            }
        }

        countryList.innerHTML = listHtml;
        alphabetIndex.innerHTML = alphabetHtml;
    }

    // Modal Control Functions
    function openCountryModal() {
        document.getElementById('countryModal').style.display = 'flex';
        document.getElementById('country_display').classList.remove('is-invalid');
    }

    function closeCountryModal(event) {
        if (!event || event.target === document.getElementById('countryModal')) {
            document.getElementById('countryModal').style.display = 'none';
        }
    }

    function selectCountry(country) {
        document.getElementById('country_input').value = country;
        document.getElementById('country_text').innerText = country;
        document.getElementById('country_text').style.color = '#2c3e50';
        closeCountryModal();
    }

    function scrollToLetter(letter) {
        const target = document.getElementById('group-' + letter);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Initialize Card Type Selection
    document.querySelectorAll('.card-type-option').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.card-type-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            document.getElementById('cardTypeSelector').classList.remove('is-invalid');
            document.getElementById('err-card_type').style.display = 'none';
        });
    });

    // Formatting while typing
    document.getElementById('card_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, ''); // Keep only digits
        this.classList.remove('is-invalid');
    });

    document.getElementById('expiry_date').addEventListener('input', function(e) {
        let val = this.value.replace(/\D/g, ''); // Keep only digits
        if (val.length > 2) {
            val = val.substring(0, 2) + '/' + val.substring(2, 4);
        }
        this.value = val;
        this.classList.remove('is-invalid');
    });

    document.getElementById('cvv').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, ''); // Keep only digits
        this.classList.remove('is-invalid');
    });

    // Remove invalid class on input
    document.querySelectorAll('input, textarea').forEach(el => {
        el.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });

    // Validation & Submission
    function validateAndSubmit() {
        let isValid = true;

        // Card Type
        const cardType = document.querySelector('input[name="card_type"]:checked');
        if (!cardType) {
            document.getElementById('cardTypeSelector').classList.add('is-invalid');
            document.getElementById('err-card_type').style.display = 'block';
            isValid = false;
        }

        // Card Number (Only Digits, 13-19 length)
        const cardNo = document.getElementById('card_number');
        if (!/^\d{13,19}$/.test(cardNo.value)) {
            cardNo.classList.add('is-invalid');
            isValid = false;
        }

        // Expiry Date (MM/YY)
        const expiry = document.getElementById('expiry_date');
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry.value)) {
            expiry.classList.add('is-invalid');
            isValid = false;
        }

        // CVV (3 or 4 digits)
        const cvv = document.getElementById('cvv');
        if (!/^\d{3,4}$/.test(cvv.value)) {
            cvv.classList.add('is-invalid');
            isValid = false;
        }

        // Full Name
        const fullName = document.getElementById('full_name');
        if (fullName.value.trim() === '') {
            fullName.classList.add('is-invalid');
            isValid = false;
        }

        // Country
        const country = document.getElementById('country_input');
        if (country.value.trim() === '') {
            document.getElementById('country_display').classList.add('is-invalid');
            document.getElementById('err-country').style.display = 'block';
            isValid = false;
        }

        // Address
        const address = document.getElementById('address');
        if (address.value.trim() === '') {
            address.classList.add('is-invalid');
            isValid = false;
        }

        if (isValid) {
            document.getElementById('paymentCardForm').submit();
        } else {
            // Scroll to the first error
            const firstError = document.querySelector('.is-invalid, .invalid-feedback[style="display: block;"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Run init on load
    document.addEventListener('DOMContentLoaded', initCountryPicker);
</script>
@endsection
