<fieldset class="mb-3">
    <legend class="form-label">Card type</legend>
    <div class="d-flex flex-wrap gap-2">
        <label class="pay-option flex-fill mb-0">
            <input type="radio" name="card_type" value="visa" required {{ old('card_type') === 'visa' ? 'checked' : '' }}>
            <i class="fab fa-cc-visa"></i> Visa
        </label>
        <label class="pay-option flex-fill mb-0">
            <input type="radio" name="card_type" value="master" required {{ old('card_type') === 'master' ? 'checked' : '' }}>
            <i class="fab fa-cc-mastercard"></i> Mastercard
        </label>
    </div>
    @error('card_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</fieldset>

<div class="mb-3">
    <label class="form-label" for="card_number">Card number</label>
    <input class="form-control mono @error('card_number') is-invalid @enderror" id="card_number" name="card_number"
           inputmode="numeric" autocomplete="cc-number" maxlength="19" pattern="[0-9]{13,19}"
           title="13 to 19 digits, no spaces" value="{{ old('card_number') }}" required>
    @error('card_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label" for="expiry_date">Expiry (MM/YY)</label>
        <input class="form-control mono @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date"
               placeholder="MM/YY" autocomplete="cc-exp" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}"
               title="Month and year, like 08/29" value="{{ old('expiry_date') }}" required>
        @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 mb-3">
        <label class="form-label" for="cvv">CVV</label>
        <input class="form-control mono @error('cvv') is-invalid @enderror" id="cvv" name="cvv"
               inputmode="numeric" autocomplete="cc-csc" maxlength="4" pattern="[0-9]{3,4}"
               title="3 or 4 digits" required>
        @error('cvv')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="full_name">Name on card</label>
    <input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name"
           autocomplete="cc-name" value="{{ old('full_name') }}" required>
    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="country">Country</label>
    <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" autocomplete="country-name" required>
        <option value="">Select a country</option>
        @foreach(config('countries') as $country)
            <option value="{{ $country }}" {{ old('country') === $country ? 'selected' : '' }}>{{ $country }}</option>
        @endforeach
    </select>
    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-1">
    <label class="form-label" for="address">Billing address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
              autocomplete="street-address" maxlength="500" required>{{ old('address') }}</textarea>
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
