<script setup>
import { usePage } from '@inertiajs/vue3'
import { reactive } from 'vue';
import { ref } from 'vue';

const page = usePage()

const app = reactive(page.props.app)
const branch = reactive(page.props.branch)
const setting = reactive(page.props.setting)
//const inwordsvalue = convertToWords(props.order.total)
//const n = convertToWords(props.order.total)
// const order = reactive(page.props.order)

const props = defineProps({
	order: Object,
})


function convertToWords2(inwordsvalue) {
    // Implement your logic to convert the total to words here
    // This is a simple example, you may need a more comprehensive solution
    // Convert 123.45 to "One Hundred Twenty Three Dollars and Forty-Five Cents"
    //inwordsvalue = 500;
    return inwordsvalue;
}
const totalAmount = ref(props.order.total);

const numberToWords = (num) => {
    if (num === 0) return 'zero';

    const belowTwenty = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    const belowHundred = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    const toWords = (n) => {
        if (n < 20) return belowTwenty[n - 1]; // handles 1 - 19
        if (n < 100) return belowHundred[Math.floor(n / 10)] + (n % 10 > 0 ? '-' + belowTwenty[n % 10 - 1] : '');
        if (n < 1000) return belowTwenty[Math.floor(n / 100) - 1] + ' hundred' + (n % 100 > 0 ? ' and ' + toWords(n % 100) : '');
        return toWords(Math.floor(n / 1000)) + ' thousand' + (n % 1000 > 0 ? ' ' + toWords(n % 1000) : '');
    };

    return toWords(num);
}
const capitalizeFirstLetter = (string) => {
    return string.charAt(0).toUpperCase() + string.slice(1);
};
console.log(props.order.payment_method_name);

const amountInWords = (amount) => {
    return capitalizeFirstLetter(numberToWords(amount));
};
const convertToWords = (n) => {
  if (n < 0) return false;
  const single_digit = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
  const double_digit = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const below_hundred = ['Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

  if (n === 0) return 'Zero';
  const translate = (n) => {
    let word = '';
    if (n < 10) {
      word = single_digit[n] + ' ';
    } else if (n < 20) {
      word = double_digit[n - 10] + ' ';
    } else if (n < 100) {
      const rem = translate(n % 10);
      word = below_hundred[(n - n % 10) / 10 - 2] + ' ' + rem;
    } else if (n < 1000) {
      word = single_digit[Math.trunc(n / 100)] + ' Hundred ' + translate(n % 100);
    } else if (n < 1000000) {
      word = translate(parseInt(n / 1000)).trim() + ' Thousand ' + translate(n % 1000);
    } else if (n < 1000000000) {
      word = translate(parseInt(n / 1000000)).trim() + ' Million ' + translate(n % 1000000);
    } else {
      word = translate(parseInt(n / 1000000000)).trim() + ' Billion ' + translate(n % 1000000000);
    }
    return word;
  };
  //console.log('Total from props:', convertToWords(n));

  return { convertToWords };
};



//const totalInWords = convertToWords(props.order.total);


</script>
<script>
export default {
  data() {
    return {
      logoUrl: this.assetUrl('img/avater.jpg')
    };
  },
  methods: {
    assetUrl(path) {
      return `${window.location.origin}/${path}`;
    }
  }
};
</script>
<template>
  
    <div class="invoice" style="height: fit-content; border: hidden;">
       
       

        <table>
            <tr>
                <!-- <td style="border: white; height: 150px; background-color: brown;">
                    <img style="height: 100px; display: block;" src="/cr_logo.png" alt="Company Logo" />
                </td> -->
                <img style="height: 120px; display: block;" src="/cr_logo.png" alt="Company Logo" />

                <td style="height: 50px; border: white; text-align: center;">
                    <div class="border-collapse w-full" style="text-align: center;">
                        <tr>
                            <h1 style="font-size: 22px;"><b>INVOICE</b> </h1>
                        </tr>
                        <tr>
                            <h1 style="margin-top: 10px;font-size: 18px; text-align: center;"><b>{{ setting.company_name }}</b></h1>
                        </tr>
                        <tr>
                            <p style="text-align: left;">{{ branch.address ?? setting.company_address }}</p>
                            <p v-show="branch.phone">Contact Number: {{ branch.phone }}</p>
                            <p v-show="setting.bin_number">{{ setting.bin_number }}</p>
                        </tr>
                     
                    </div>
                </td>
            </tr>
        </table>






        <!-- <table class="border-collapse w-full">
            <tbody>
                <tr v-if="app.logo_invoice">
                    <td class="align-middle text-xs font-normal" scope="col">
                        <img class="mx-auto h-24 w-auto uppercase-24" :src="app.logo_invoice" alt="company_logo" />
                    </td>
                </tr>
                <tr>
                    <td class="align-middle text-xs font-normal" scope="col">
                        <h1 class="w-full text-2xl font-semibold text-center">{{ setting.company_name }}</h1>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle text-sm font-normal" scope="col">
                        <p class="mx-auto text-center tracking-wider">{{ branch.address ?? setting.company_address }}</p>
                    </td>
                </tr>
                <tr v-show="branch.phone">
                    <td class="align-middle text-sm font-normal" scope="col">
                        <p class="mx-auto uppercase tracking-wider text-center">Contact Number: {{ branch.phone }}</p>
                    </td>
                </tr>
                <tr v-show="setting.bin_number">
                    <td class="align-middle text-sm font-normal" scope="col">
                        <p class="mx-auto uppercase tracking-widest text-center">{{ setting.bin_number }}</p>
                    </td>
                </tr>
                <tr>
                    <th class="pt-4 align-middle text-sm font-bold uppercase tracking-wider text-center" colspan="2" scope="col">
                        ({{ props.order.status != 'complete' ? 'Bill' : 'Money Receipt' }})
                    </th>
                </tr>
            </tbody>
        </table> -->
        <!-- <h2 class="header">
            {{ props.order.status != 'complete' ? 'Bill' : 'Money Receipt' }}
        </h2> -->
        <div style="margin-bottom: 20px;">
            <table >
                <td style="border: none; align-content: left; text-align: left;">
                    <tr>
                        <p style="font-size: 12px;">
                            <span style="margin-right: 22px;">Customer Name:</span>
                            <b>{{ props.order.customer_name ?? 'N/A' }}</b>
                        </p>

                    </tr>
                    <tr>
                        <p style="font-size: 12px;">
                            <span style="margin-right: 10px;">Customer Number:</span>
                            <b>{{ props.order.customer_mobile ?? 'N/A' }}</b>
                        </p>

                    </tr>
                    <tr>
                        <p v-show="props.order.delivery_address" style="font-size: 12px;">
    <span style="margin-right: 18px;">Delivery Address:</span>
    <b>{{ props.order.delivery_address ?? 'N/A' }}</b>
</p>
                    
                    </tr>
                    
                </td>
                <td style="border: none; text-align: end;">
                    <!-- <p>Date: {{ props.order.datetime_format }}</p> -->
                    <p  style="font-size: 12px;">
                        <span style="margin-right: 10px;"><b>Date:</b></span>
                        <span>{{ props.order.datetime_format ?? 'N/A' }}</span>
                    </p>


                    <p  style="font-size: 12px;">
                        <span style="margin-right: 10px;"><b>Invoice number:</b></span>
                        <span>#{{ props.order.invoice_number ?? 'N/A' }}</span>
                    </p>
                    <p style="font-size: 12px;">
                        <span style="margin-right: 10px;"><b>Mode of payment:</b></span>
                        <span v-if="props.order.payment_method_name">{{ props.order.payment_method_name }}</span>
                        <span v-else>N/A</span>
                    </p>
                    <!-- <p>Invoice number:# {{ props.order.invoice_number }}</p> -->
                    <!-- <p>Cashier: {{ props.order.creator_name }}</p> -->
                    <!-- <p>Waiter: {{ props.order.waiter_name }}</p>
                    <p>Table No: {{ props.order.table_name ?? 'N/A' }}</p> -->
                    <!-- <p>Mode of payment: {{ props.order.payment_method_name ?? 'N/A' }}</p> -->
                </td>
            </table>
          
           
        </div>
        <!-- <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in props.order.products" :key="index">
                    <td>{{ (index + 1)+ ". " + item.product_name }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>{{ item.rate }}</td>
                    <td>{{ item.total }}</td>
                </tr>
                <tr>
                    <td  class="text-lg" colspan="3">Sub Total:</td>
                    <td>{{ Number(props.order.sub_total).toFixed(2) }}</td>
                </tr>
                <tr>
                    <td class="text-lg" colspan="3">Discount:</td>
                    <td>{{ Number(props.order.discount_amount).toFixed(2) }}</td>
                </tr>
                <tr v-if="props.order.service_cost > 0">
                    <td class="text-lg" colspan="3">Service Cost:</td>
                    <td>{{ Number(props.order.service_cost).toFixed(2) }}</td>
                </tr>
                <tr>
                    <td class="text-lg" colspan="3">VAT:</td>
                    <td>{{ Number(props.order.vat_amount).toFixed(2) }}</td>
                </tr>
                <tr>
                    <td class="text-lg" colspan="3">Total:</td>
                    <td >{{ Number(props.order.total).toFixed(2) }}</td>
                </tr>
                <tr v-if="props.order.status == 'complete'">
                    <td class="text-lg" colspan="3">Cash:</td>
                    <td>{{ Number(props.order.cash).toFixed(2) }}</td>
                </tr>
                <tr v-if="props.order.status == 'complete'">
                    <td class="text-lg" colspan="3">Change:</td>
                    <td>{{ Number(props.order.change).toFixed(2) }}</td>
                </tr>
            </tbody>
          <div>Total in words: {{ convertToWords(n) }}</div> -->
        
          <!-- <p>Total in words: {{ convertToWords2(props.order.total) }}</p> -->
          <!-- <p>Total box: {{ (props.order.total_box) }}</p>
        </table> -->

        <div class="table-container" style="page-break-inside: avoid;">
            <table style="margin-left: .7in; width: 90%;">
                <thead >
                    <tr style="background-color: grey;">
                        <th>S.N.</th>
                        <th style="width: 300px;">Description</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in props.order.products" :key="index">
                        <td>{{ index + 1 }}</td> <!-- This will display the serial number -->
                        <td style="text-align: left;">{{item.product_name }}</td>
                        <td>{{ item.quantity }}</td>
                        <td class="amount-column">{{ item.rate }}</td>
                        <td class="amount-column">{{ item.total }}</td>
                    </tr>
                    <!-- <tr >
                        <td colspan="3" class="text-lg" style="border-left: hidden; border-right: hidden; font-size: 12px;">Sub Total:</td>
                        <td class="text-lg" style="border-right: hidden;  font-size: 12px;">{{ Number(props.order.sub_total).toFixed(2) }} Tk.</td>
                    </tr> -->
                    <tr >
                        <td colspan="4" class="text-lg" style="border-left: hidden; border-right: hidden; font-size: 12px;"></td>
                        <td class="text-lg" style="border-right: hidden;  font-size: 12px;"></td>
                    </tr>
                    <tr class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr" style="padding-top: 10px;">Sub Total:</td>
                        <td class="custom-td-tr" style="padding-top: 10px;"><b>{{ Number(props.order.sub_total).toFixed(2) }}</b></td>
                    </tr>
                    <tr class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr" style="padding-top: 10px;">Discount:</td>
                        <td class="custom-td-tr" style="padding-top: 10px;">{{ Number(props.order.discount_amount).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="props.order.service_cost > 0" class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr">Service Cost:</td>
                        <td class="custom-td-tr">{{ Number(props.order.service_cost).toFixed(2) }}</td>
                    </tr>
                    <!-- <tr class="custom-td-tr">
                        <td colspan="3" class="custom-td-tr">VAT:</td>
                        <td class="amount-column">{{ Number(props.order.vat_amount).toFixed(2) }}</td>
                    </tr> -->
                    <tr class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr">Payable Amount:</td>
                        <td class="custom-td-tr">{{ Number(props.order.total).toFixed(2) }}</td>
                    </tr>
                    <tr  style="border: hidden; padding-bottom: 20px;">
                        <td colspan="4" class="custom-td-tr">Paid Amount:</td>
                        <td class="custom-td-tr">{{ Number(props.order.cash).toFixed(2) }}</td>
                    </tr>
                    <tr style="margin-top: 0px;"></tr>
                    <!-- <tr v-if="props.order.status == 'complete'" class="custom-td-tr">
                        <td colspan="3" class="custom-td-tr">Change:</td>
                        <td class="custom-td-tr">{{ Number(props.order.change).toFixed(2) }}</td>
                    </tr> -->
                    <tr style="border: hidden;">
                        <td colspan="5">
                            <div style="width: 30%; margin-left: auto; margin-right: 0; background-color: black;">
                                <hr>
                            </div>
                        </td>
                    </tr>


                    <tr class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr" style="padding-top: 0px;">Due amount:</td>
                        <td class="custom-td-tr" style="padding-top: 0px;">
                            <b>{{ (Number(props.order.cash) > Number(props.order.total)) ? '0.00' : (Number(props.order.total) - Number(props.order.cash)).toFixed(2) }}</b>
                        </td>
                    </tr>

                </tbody>
        
            </table>
                
            <div style="margin-top: 20px; margin-left: 0.6in;">
                <!-- <div>Total in words: {{ convertToWords(props.order.total) }}</div> -->
                <p>Total box: {{ (props.order.total_box) }}</p>
                <p>In Words: {{ amountInWords(totalAmount) }} only<small>(including vat).</small></p>
            </div>
               

            <div style="margin-top: 150px; margin-left: 0.6in;">
                <!-- <div>Total in words: {{ convertToWords(props.order.total) }}</div> -->
                <p style="text-decoration: underline;">Terms & Conditions</p>
                <span></span>
                <p>{{props.order.note}}</p>
            </div>
        </div>
        <!-- <table style="width: 100%;margin-top: 70px; border: none;">
                <tr>
                    <td style="text-align: left; border: none; padding: 0;">Customer Signature.............................</td>
                    <td style="text-align: right; border: none; padding: 0;">Authorized Signature............................</td>
                </tr>
            </table>
        <div class="footer" style="text-align: center; margin-top: 70px;">
            
            <p v-show="props.order.note" style="position: relative; text-align: left;">Note: {{ props.order.note }}</p>
           
        </div> -->

        <div style="position: fixed; bottom: 5%; width: 100%;">
    <table style="width: 100%; border: none; ">
        <tr>
            <td style="width: 50%; text-align: left; border: none;">Customer Signature.............................</td>
            <td style="width: 50%; text-align: right; border: none; margin-left: 35px;">Authorized Signature............................</td>
            <td style="width: 50%; text-align: right; border: none; margin-left: 35px;"></td>

        </tr>
    </table>
    <table></table>
    <div class="footer" style="text-align: center; margin-top: 20px;">
        <p style="text-align: center; font-size: 8px;"><small>Note: This Invoice is generated by system</small></p>
    </div>
</div>

    </div>

    <div class="page-break"></div>

    <div class="invoice" style="height: fit-content; border: hidden;">
       
       

       <table>
           <tr>
               <!-- <td style="border: white; height: 150px; background-color: brown;">
                   <img style="height: 100px; display: block;" src="/cr_logo.png" alt="Company Logo" />
               </td> -->
               <img style="height: 120px; display: block;" src="/cr_logo.png" alt="Company Logo" />

               <td style="height: 50px; border: white; text-align: center;">
                   <div class="border-collapse w-full" style="text-align: center;">
                       <tr>
                           <h1 style="font-size: 22px;"><b>INVOICE</b> </h1>
                       </tr>
                       <tr>
                           <h1 style="margin-top: 10px;font-size: 18px; text-align: center;"><b>{{ setting.company_name }}</b></h1>
                       </tr>
                       <tr>
                           <p style="text-align: left;">{{ branch.address ?? setting.company_address }}</p>
                           <p v-show="branch.phone">Contact Number: {{ branch.phone }}</p>
                           <p v-show="setting.bin_number">{{ setting.bin_number }}</p>
                       </tr>
                    
                   </div>
               </td>
           </tr>
       </table>






       <!-- <table class="border-collapse w-full">
           <tbody>
               <tr v-if="app.logo_invoice">
                   <td class="align-middle text-xs font-normal" scope="col">
                       <img class="mx-auto h-24 w-auto uppercase-24" :src="app.logo_invoice" alt="company_logo" />
                   </td>
               </tr>
               <tr>
                   <td class="align-middle text-xs font-normal" scope="col">
                       <h1 class="w-full text-2xl font-semibold text-center">{{ setting.company_name }}</h1>
                   </td>
               </tr>
               <tr>
                   <td class="align-middle text-sm font-normal" scope="col">
                       <p class="mx-auto text-center tracking-wider">{{ branch.address ?? setting.company_address }}</p>
                   </td>
               </tr>
               <tr v-show="branch.phone">
                   <td class="align-middle text-sm font-normal" scope="col">
                       <p class="mx-auto uppercase tracking-wider text-center">Contact Number: {{ branch.phone }}</p>
                   </td>
               </tr>
               <tr v-show="setting.bin_number">
                   <td class="align-middle text-sm font-normal" scope="col">
                       <p class="mx-auto uppercase tracking-widest text-center">{{ setting.bin_number }}</p>
                   </td>
               </tr>
               <tr>
                   <th class="pt-4 align-middle text-sm font-bold uppercase tracking-wider text-center" colspan="2" scope="col">
                       ({{ props.order.status != 'complete' ? 'Bill' : 'Money Receipt' }})
                   </th>
               </tr>
           </tbody>
       </table> -->
       <!-- <h2 class="header">
           {{ props.order.status != 'complete' ? 'Bill' : 'Money Receipt' }}
       </h2> -->
       <div style="margin-bottom: 20px;">
           <table >
               <td style="border: none; align-content: left; text-align: left;">
                   <tr>
                       <p style="font-size: 12px;">
                           <span style="margin-right: 22px;">Customer Name:</span>
                           <b>{{ props.order.customer_name ?? 'N/A' }}</b>
                       </p>

                   </tr>
                   <tr>
                       <p style="font-size: 12px;">
                           <span style="margin-right: 10px;">Customer Number:</span>
                           <b>{{ props.order.customer_mobile ?? 'N/A' }}</b>
                       </p>

                   </tr>
                   <tr>
                       <p v-show="props.order.delivery_address" style="font-size: 12px;">
   <span style="margin-right: 18px;">Delivery Address:</span>
   <b>{{ props.order.delivery_address ?? 'N/A' }}</b>
</p>
                   
                   </tr>
                   
               </td>
               <td style="border: none; text-align: end;">
                   <!-- <p>Date: {{ props.order.datetime_format }}</p> -->
                   <p  style="font-size: 12px;">
                       <span style="margin-right: 10px;"><b>Date:</b></span>
                       <span>{{ props.order.datetime_format ?? 'N/A' }}</span>
                   </p>


                   <p  style="font-size: 12px;">
                       <span style="margin-right: 10px;"><b>Invoice number:</b></span>
                       <span>#{{ props.order.invoice_number ?? 'N/A' }}</span>
                   </p>
                   <p style="font-size: 12px;">
    <span style="margin-right: 10px;"><b>Mode of payment:</b></span>
    <span v-if="props.order.payment_method_name">{{ props.order.payment_method_name }}</span>
    <span v-else>N/A</span>
</p>
                    
                   
                   <!-- <p>Invoice number:# {{ props.order.invoice_number }}</p> -->
                   <!-- <p>Cashier: {{ props.order.creator_name }}</p> -->
                   <!-- <p>Waiter: {{ props.order.waiter_name }}</p>
                   <p>Table No: {{ props.order.table_name ?? 'N/A' }}</p> -->
                   <!-- <p>Mode of payment: {{ props.order.payment_method_name ?? 'N/A' }}</p> -->
               </td>
           </table>
         
          
       </div>
       <!-- <table>
           <thead>
               <tr>
                   <th>Description</th>
                   <th>Qty</th>
                   <th>Rate</th>
                   <th>Amount</th>
               </tr>
           </thead>
           <tbody>
               <tr v-for="(item, index) in props.order.products" :key="index">
                   <td>{{ (index + 1)+ ". " + item.product_name }}</td>
                   <td>{{ item.quantity }}</td>
                   <td>{{ item.rate }}</td>
                   <td>{{ item.total }}</td>
               </tr>
               <tr>
                   <td  class="text-lg" colspan="3">Sub Total:</td>
                   <td>{{ Number(props.order.sub_total).toFixed(2) }}</td>
               </tr>
               <tr>
                   <td class="text-lg" colspan="3">Discount:</td>
                   <td>{{ Number(props.order.discount_amount).toFixed(2) }}</td>
               </tr>
               <tr v-if="props.order.service_cost > 0">
                   <td class="text-lg" colspan="3">Service Cost:</td>
                   <td>{{ Number(props.order.service_cost).toFixed(2) }}</td>
               </tr>
               <tr>
                   <td class="text-lg" colspan="3">VAT:</td>
                   <td>{{ Number(props.order.vat_amount).toFixed(2) }}</td>
               </tr>
               <tr>
                   <td class="text-lg" colspan="3">Total:</td>
                   <td >{{ Number(props.order.total).toFixed(2) }}</td>
               </tr>
               <tr v-if="props.order.status == 'complete'">
                   <td class="text-lg" colspan="3">Cash:</td>
                   <td>{{ Number(props.order.cash).toFixed(2) }}</td>
               </tr>
               <tr v-if="props.order.status == 'complete'">
                   <td class="text-lg" colspan="3">Change:</td>
                   <td>{{ Number(props.order.change).toFixed(2) }}</td>
               </tr>
           </tbody>
         <div>Total in words: {{ convertToWords(n) }}</div> -->
       
         <!-- <p>Total in words: {{ convertToWords2(props.order.total) }}</p> -->
         <!-- <p>Total box: {{ (props.order.total_box) }}</p>
       </table> -->

       <div class="table-container" style="page-break-inside: avoid;">
           <table style="margin-left: .7in; width: 90%;">
               <thead >
                   <tr style="background-color: grey;">
                       <th>S.N.</th>
                       <th style="width: 300px;">Description</th>
                       <th>Qty</th>
                       <th>Rate</th>
                       <th>Amount</th>
                   </tr>
               </thead>
               <tbody>
                   <tr v-for="(item, index) in props.order.products" :key="index">
                       <td>{{ index + 1 }}</td> <!-- This will display the serial number -->
                       <td style="text-align: left;">{{item.product_name }}</td>
                       <td>{{ item.quantity }}</td>
                       <td class="amount-column">{{ item.rate }}</td>
                       <td class="amount-column">{{ item.total }}</td>
                   </tr>
                   <!-- <tr >
                       <td colspan="3" class="text-lg" style="border-left: hidden; border-right: hidden; font-size: 12px;">Sub Total:</td>
                       <td class="text-lg" style="border-right: hidden;  font-size: 12px;">{{ Number(props.order.sub_total).toFixed(2) }} Tk.</td>
                   </tr> -->
                   <tr >
                       <td colspan="4" class="text-lg" style="border-left: hidden; border-right: hidden; font-size: 12px;"></td>
                       <td class="text-lg" style="border-right: hidden;  font-size: 12px;"></td>
                   </tr>
                   <tr class="custom-td-tr">
                       <td colspan="4" class="custom-td-tr" style="padding-top: 10px;">Sub Total:</td>
                       <td class="custom-td-tr" style="padding-top: 10px;"><b>{{ Number(props.order.sub_total).toFixed(2) }}</b></td>
                   </tr>
                   <tr class="custom-td-tr">
                       <td colspan="4" class="custom-td-tr" style="padding-top: 10px;">Discount:</td>
                       <td class="custom-td-tr" style="padding-top: 10px;">{{ Number(props.order.discount_amount).toFixed(2) }}</td>
                   </tr>
                   <tr v-if="props.order.service_cost > 0" class="custom-td-tr">
                       <td colspan="4" class="custom-td-tr">Service Cost:</td>
                       <td class="custom-td-tr">{{ Number(props.order.service_cost).toFixed(2) }}</td>
                   </tr>
                   <!-- <tr class="custom-td-tr">
                       <td colspan="3" class="custom-td-tr">VAT:</td>
                       <td class="amount-column">{{ Number(props.order.vat_amount).toFixed(2) }}</td>
                   </tr> -->
                   <tr class="custom-td-tr">
                       <td colspan="4" class="custom-td-tr">Payable Amount:</td>
                       <td class="custom-td-tr">{{ Number(props.order.total).toFixed(2) }}</td>
                   </tr>
                   <tr  style="border: hidden; padding-bottom: 20px;">
                       <td colspan="4" class="custom-td-tr">Paid Amount:</td>
                       <td class="custom-td-tr">{{ Number(props.order.cash).toFixed(2) }}</td>
                   </tr>
                   <tr style="margin-top: 0px;"></tr>
                   <!-- <tr v-if="props.order.status == 'complete'" class="custom-td-tr">
                       <td colspan="3" class="custom-td-tr">Change:</td>
                       <td class="custom-td-tr">{{ Number(props.order.change).toFixed(2) }}</td>
                   </tr> -->
                   <tr style="border: hidden;">
                       <td colspan="5">
                           <div style="width: 30%; margin-left: auto; margin-right: 0; background-color: black;">
                               <hr>
                           </div>
                       </td>
                   </tr>


                   <tr class="custom-td-tr">
                        <td colspan="4" class="custom-td-tr" style="padding-top: 0px;">Due amount:</td>
                        <td class="custom-td-tr" style="padding-top: 0px;">
                            <b>{{ (Number(props.order.cash) > Number(props.order.total)) ? '0.00' : (Number(props.order.total) - Number(props.order.cash)).toFixed(2) }}</b>
                        </td>
                    </tr>


               </tbody>
       
           </table>
               
           <div style="margin-top: 20px; margin-left: 0.6in;">
               <!-- <div>Total in words: {{ convertToWords(props.order.total) }}</div> -->
               <p>Total box: {{ (props.order.total_box) }}</p>
               <p>In Words: {{ amountInWords(totalAmount) }} only<small>(including vat).</small></p>
           </div>
              

           <div style="margin-top: 150px; margin-left: 0.6in;">
               <!-- <div>Total in words: {{ convertToWords(props.order.total) }}</div> -->
               <p style="text-decoration: underline;">Terms & Conditions</p>
               <span></span>
               <p>{{props.order.note}}</p>
           </div>
       </div>
       <!-- <table style="width: 100%;margin-top: 70px; border: none;">
               <tr>
                   <td style="text-align: left; border: none; padding: 0;">Customer Signature.............................</td>
                   <td style="text-align: right; border: none; padding: 0;">Authorized Signature............................</td>
               </tr>
           </table>
       <div class="footer" style="text-align: center; margin-top: 70px;">
           
           <p v-show="props.order.note" style="position: relative; text-align: left;">Note: {{ props.order.note }}</p>
          
       </div> -->

       <div style="position: fixed; bottom: 5%; width: 100%;">
   <table style="width: 100%; border: none; ">
       <tr>
           <td style="width: 50%; text-align: left; border: none;">Customer Signature.............................</td>
           <td style="width: 50%; text-align: right; border: none; margin-left: 35px;">Authorized Signature............................</td>
           <td style="width: 50%; text-align: right; border: none; margin-left: 35px;"></td>

       </tr>
   </table>
   <table></table>
   <div class="footer" style="text-align: center; margin-top: 20px;">
       <p style="text-align: center; font-size: 8px;"><small>Note: This Invoice is generated by system</small></p>
   </div>
</div>

   </div>
</template>

<style>
        body {
            font-family: Arial, sans-serif;
        }

     .text-lg {
        padding: 2px 8px; /* Reduced vertical padding */
            text-align: right;
        }
        .invoice {
            width: 210mm;
            height: 297mm;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .company-details {
            text-align: right;
            max-width: 200px;
        }
        .company-details img {
            max-width: 100%; /* Ensure the image does not exceed the width */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            text-align: center;
            font-size: 14px;
            padding: 8px; /* Reduced padding */

        }
        
        thead th {
            padding: 2px 8px; /* Reduced vertical padding */
            text-align: center;
            background-color: grey; /* If you want to apply the same background to all headers */
        }
        .custom-td-tr{
            text-align: right;
            border: hidden;
            font-size: 12px;
            line-height: .5;             /* Adjust line gap as needed */
            padding-bottom: 2px; 
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .table-container {
        margin-left: .0in;
        width: 100%;
        }

        .amount-column {
            text-align: right;
        }
        /* @page {
        size: A4;
        margin: 20mm;
    } */

    /* Control page breaks */
    .page-break {
        page-break-before: always;
    }
    
    </style>