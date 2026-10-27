// JavaScript
document.getElementById('payment-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally
  
    // Get the form data
    var name = document.getElementById('name').value;
    var cardNumber = document.getElementById('card-number').value;
    var expirationDate = document.getElementById('expiration-date').value;
    var cvv = document.getElementById('cvv').value;
    var amount = document.getElementById('amount').value;
  
    // Validate the form data (you can add more validation rules here)
    if (name.trim() === '' || cardNumber.trim() === '' || expirationDate.trim() === '' || cvv.trim() === '' || amount.trim() === '') {
      alert('Please fill in all the required fields.');
      return;
    }
  
    // Send the payment data to the backend
    fetch('/process-payment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: name,
        cardNumber: cardNumber,
        expirationDate: expirationDate,
        cvv: cvv,
        amount: amount
      })
    })
    .then(function(response) {
      if (response.ok) {
        console.log('Payment successful!');
        // Reset the form
        document.getElementById('payment-form').reset();
      } else {
        console.error('Payment failed.');
      }
    })
    .catch(function(error) {
      console.error('Error processing payment:', error);
    });
  });

