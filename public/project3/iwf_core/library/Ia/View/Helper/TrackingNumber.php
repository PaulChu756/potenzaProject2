<?php

class Ia_View_Helper_TrackingNumber extends Zend_View_Helper_Abstract
{

    /**
     * Returns a truncated string at the length specified
     *
     * @param string $string The string to truncate
     * @param int $length The maximum number of characters the string should be
     * @param string $etc The string to append to the end of the truncated string
     * @return string The truncated string
     */
    public function trackingNumber($tracking_number, $carrier)
    {
        switch($carrier){
            case 'UPS':
                return '<a href="http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;
            case 'FedEx':
                return '<a href="https://www.fedex.com/apps/fedextrack/?cntry_code=us&tracknumbers='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;
            case 'USPS':
                return '<a href="https://tools.usps.com/go/TrackConfirmAction!input.action?tLabels='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;
            case 'Saia':
                return '<a href="http://www.saia.com/V2/tracing/mnf2.aspx?m=2&PRONum1='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;
            case 'Old Dominion':
                return '<a href="http://www.odfl.com/trace/Trace.jsp?pronum='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;   
            case 'WRDS':
                return '<a href="http://tracking.wrds.com/zTrack2.aspx?ProNumber='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;  
            case 'DeSantis':
                $modalXhtml = '
                <div class="modal fade" id="'.$tracking_number.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h3>DeSantis</h3>
                      </div>
                      <div class="modal-body">
                        <p style="text-align:center">Please call <strong>1-973-491-5455</strong> and provide the following tracking number:</p>
                        <div class="well">
                        <h2 style="text-align:center">'.$tracking_number.'</h2>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <a href="#" class="btn" data-dismiss="modal">Close</a>
                      </div>
                    </div>
                  </div>
                </div>';
                return $modalXhtml.'<a data-toggle="modal" href="#'.$tracking_number.'">'.$tracking_number.'</a>';
                break;
            case 'OnTrac':
                return '<a href="http://www.ontrac.com/tracking.asp?trackingres=submit&tracking_number='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;  
            default:
                return '<a href="https://www.google.com/#q='.$tracking_number.'" rel="external" target="_blank">'.$tracking_number.'</a>';
                break;                                   

        }
    }
}