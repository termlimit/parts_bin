/* calculate loan repayments */
function loancalc() { //set up variables
  var price, depositpercent, depositpercent, rate, term, deposit, loan, interest, monthlypayment, totalprice
  price = <?php echo $price; ?>;  // copy php variable to js variable
  depositpercent = (0.1);  // % 
  rate = 3.25;  // % 
  term = 5; // years
  deposit = price * depositpercent; deposit = deposit.toFixed(0); //deposit payable
  loan = price - deposit; // amount to be financed	
  interest = ((loan * rate * term)/100);  // interest
  monthlypayment = ((loan + interest)/(term*12)); monthlypayment = monthlypayment.toFixed(2); monthlypayment = numberWithCommas(monthlypayment);// monthly payments 
  totalprice = price + interest; totalprice = totalprice.toFixed(0); totalprice = numberWithCommas(totalprice); // total payable

  //write load calculation to loancalcDiv
  document.getElementById("loancalcDiv").innerHTML = ('<h2>Depending upon your financial circumstances you could have this vehicle for &pound;<strong>' + monthlypayment + ' per month.</strong></h2>'
  + '<p>Deposit: &pound;' + deposit +' (' + depositpercent*100 +'%), Amount of Credit: &pound;' + loan +', Term: ' + term +' years, Interest Payable: &pound;' + interest.toFixed(0) +' (APR: ' + rate +'%) - <strong>Total Amount Payable: &pound;' + totalprice +'</strong></p>');
}

/* format currency commas */
function numberWithCommas(n) {
  var parts=n.toString().split(".");
  return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
}
