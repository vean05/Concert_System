<div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="addCardTitle">Add a card</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCardForm">
                <div class="modal-body">
                    <div class="alert alert-danger" id="addCardError" hidden></div>
                    @include('payment_cards._fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Save card</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('addCardForm');
    const errorBox = document.getElementById('addCardError');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        errorBox.hidden = true;

        fetch(@json(route('payment_cards.storeAjax')), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new FormData(form),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    const errors = result.data.errors ? Object.values(result.data.errors) : [['The card could not be saved.']];
                    errorBox.textContent = errors[0][0];
                    errorBox.hidden = false;
                    return;
                }

                const card = result.data.card;
                const option = document.createElement('label');
                option.className = 'pay-option';
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'payment_card_id';
                radio.value = card.id;
                radio.checked = true;
                const icon = document.createElement('i');
                icon.className = 'far fa-credit-card';
                const text = document.createElement('span');
                text.textContent = card.brand + ' ending in ' + card.last_four;
                option.append(radio, icon, text);
                document.getElementById('cardList').append(option);
                document.getElementById('noCards').hidden = true;

                form.reset();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addCardModal')).hide();
                document.dispatchEvent(new Event('card-added'));
            })
            .catch(function () {
                errorBox.textContent = 'The card could not be saved. Please try again.';
                errorBox.hidden = false;
            });
    });
})();
</script>
@endpush
