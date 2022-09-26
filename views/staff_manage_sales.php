<?php
/*
 * Created on Tue Feb 15 2022
 *
 *  Devlan Agency - devlan.co.ke 
 *
 * hello@devlan.co.ke
 *
 *
 * The Devlan End User License Agreement
 *
 * Copyright (c) 2022 Devlan Agency
 *
 * 1. GRANT OF LICENSE
 * Devlan Agency hereby grants to you (an individual) the revocable, personal, non-exclusive, and nontransferable right to
 * install and activate this system on two separated computers solely for your personal and non-commercial use,
 * unless you have purchased a commercial license from Devlan Agency. Sharing this Software with other individuals, 
 * or allowing other individuals to view the contents of this Software, is in violation of this license.
 * You may not make the Software available on a network, or in any way provide the Software to multiple users
 * unless you have first purchased at least a multi-user license from Devlan Agency.
 *
 * 2. COPYRIGHT 
 * The Software is owned by Devlan Agency and protected by copyright law and international copyright treaties. 
 * You may not remove or conceal any proprietary notices, labels or marks from the Software.
 *
 * 3. RESTRICTIONS ON USE
 * You may not, and you may not permit others to
 * (a) reverse engineer, decompile, decode, decrypt, disassemble, or in any way derive source code from, the Software;
 * (b) modify, distribute, or create derivative works of the Software;
 * (c) copy (other than one back-up copy), distribute, publicly display, transmit, sell, rent, lease or 
 * otherwise exploit the Software.  
 *
 * 4. TERM
 * This License is effective until terminated. 
 * You may terminate it at any time by destroying the Software, together with all copies thereof.
 * This License will also terminate if you fail to comply with any term or condition of this Agreement.
 * Upon such termination, you agree to destroy the Software, together with all copies thereof.
 *
 * 5. NO OTHER WARRANTIES. 
 * DEVLAN AGENCY  DOES NOT WARRANT THAT THE SOFTWARE IS ERROR FREE. 
 * DEVLAN AGENCY SOFTWARE DISCLAIMS ALL OTHER WARRANTIES WITH RESPECT TO THE SOFTWARE, 
 * EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT OF THIRD PARTY RIGHTS. 
 * SOME JURISDICTIONS DO NOT ALLOW THE EXCLUSION OF IMPLIED WARRANTIES OR LIMITATIONS
 * ON HOW LONG AN IMPLIED WARRANTY MAY LAST, OR THE EXCLUSION OR LIMITATION OF 
 * INCIDENTAL OR CONSEQUENTIAL DAMAGES,
 * SO THE ABOVE LIMITATIONS OR EXCLUSIONS MAY NOT APPLY TO YOU. 
 * THIS WARRANTY GIVES YOU SPECIFIC LEGAL RIGHTS AND YOU MAY ALSO 
 * HAVE OTHER RIGHTS WHICH VARY FROM JURISDICTION TO JURISDICTION.
 *
 * 6. SEVERABILITY
 * In the event of invalidity of any provision of this license, the parties agree that such invalidity shall not
 * affect the validity of the remaining portions of this license.
 *
 * 7. NO LIABILITY FOR CONSEQUENTIAL DAMAGES IN NO EVENT SHALL DEVLAN AGENCY  OR ITS SUPPLIERS BE LIABLE TO YOU FOR ANY
 * CONSEQUENTIAL, SPECIAL, INCIDENTAL OR INDIRECT DAMAGES OF ANY KIND ARISING OUT OF THE DELIVERY, PERFORMANCE OR 
 * USE OF THE SOFTWARE, EVEN IF DEVLAN HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES
 * IN NO EVENT WILL DEVLAN  LIABILITY FOR ANY CLAIM, WHETHER IN CONTRACT 
 * TORT OR ANY OTHER THEORY OF LIABILITY, EXCEED THE LICENSE FEE PAID BY YOU, IF ANY.
 */

session_start();
require_once('../config/config.php');
require_once('../config/checklogin.php');
require_once('../config/codeGen.php');
check_login();
/* Cancel sale */
if (isset($_POST['cancel_sale'])) {
    $sale_id = $_POST['sale_id'];
    $product_id = $_POST['product_id'];
    $sale_quantity = $_POST['sale_quantity'];
    $sale_details = $_POST['sale_details'];
    $user_id = $_SESSION['user_id'];
    $user_password = sha1(md5($_POST['user_password']));
    /* Activity Logged */
    $log_type = "Cancelled $sale_details";

    /* Check if the password matches with record */
    $ret = "SELECT * FROM  users WHERE user_id  ='$user_id'";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($user = $res->fetch_object()) {
        /* Entered password */
        $password_from_db = $user->user_password;
        if ($user_password == $password_from_db) {

            $ret = "SELECT * FROM  products  WHERE product_id ='$product_id'";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute(); //ok
            $res = $stmt->get_result();
            while ($product = $res->fetch_object()) {
                $new_stock = $product->product_quantity + $sale_quantity;

                $query2 = 'UPDATE products SET  product_quantity=? WHERE product_id =?';
                $stmt2 = $mysqli->prepare($query2);
                $rc2 = $stmt2->bind_param('ss',  $new_stock, $product_id);
                $stmt2->execute();

                $query3 = 'DELETE FROM sales WHERE sale_id=? AND sale_product_id=?';
                $stmt3 = $mysqli->prepare($query3);
                $rc3 = $stmt3->bind_param('ss', $sale_id, $product_id);
                $stmt3->execute();
            }
            /* Load Log Helper */
            require_once('../functions/logs.php');
            if ($stmt3 && $stmt2) {
                $success = "$sale_details is Cancelled";
            } else {
                //inject alert that task failed
                $err = 'Please Try Again Or Try Later';
            }
        } else {
            $err = 'Wrong password. Try again';
        }
    }
}

/* Load Header Partial */
require_once('../partials/head.php')
?>

<body>
    <!-- Pre-loader start -->
    <?php require_once('../partials/preloader.php'); ?>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <!-- Top Navigation Bar -->
            <?php require_once('../partials/staff_topbar.php'); ?>

            <div class="pcoded-main-container">
                <div class="">
                    <!-- Sidebar -->
                    <div class="">
                        <!-- Page-header start -->
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Sales</h5>
                                            <p class="m-b-0">Manage Sale</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="staff_dashboard"> <i class="fa fa-home"></i> </a>
                                            </li>
                                            <li class="breadcrumb-item">
                                                <a href=""> Sales </a>
                                            </li>
                                            <li class="breadcrumb-item"><a href="">Manage Sale</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Page-header end -->
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Select On Any Sale To Cancel </h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <table class="table table-bordered dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product name</th>
                                                                    <th>Quantity</th>
                                                                    <th> Receipt No.</th>
                                                                    <th>Date of sale</th>
                                                                    <th>Manage</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $user_id = $_SESSION['user_id'];
                                                                $ret = "SELECT * FROM sales s
                                                                INNER JOIN products p ON p.product_id = s.sale_product_id
                                                                WHERE s.sale_user_id = '$user_id'";
                                                                $stmt = $mysqli->prepare($ret);
                                                                $stmt->execute(); //ok
                                                                $res = $stmt->get_result();
                                                                while ($sales = $res->fetch_object()) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $sales->product_name; ?></td>
                                                                        <td><?php echo $sales->sale_quantity; ?></td>
                                                                        <td><?php echo $sales->sale_receipt_no; ?></td>
                                                                        <td><?php echo date('d M Y g:ia', strtotime($sales->sale_datetime)); ?></td>
                                                                        <td>
                                                                            <a data-toggle="modal" href="#cancel_<?php echo $sales->sale_id; ?>" class="badge badge-danger"><i class="fas fa-ban"></i> Cancel</a>
                                                                        </td>
                                                                        <!-- Cancel sale Modal -->
                                                                        <div class="modal fade" id="cancel_<?php echo $sales->sale_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title" id="exampleModalLabel">CONFIRM CANCEL</h5>
                                                                                        <button type="button" class="close" data-dismiss="modal">
                                                                                            <span>&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <form method="POST">
                                                                                        <div class="modal-body text-center text-danger">
                                                                                            <h4>
                                                                                                Heads Up, You Are About To Cancel Reciept No : <?php echo $sales->sale_receipt_no . ' For ' . $sales->product_name; ?> Sale Record
                                                                                                <hr>
                                                                                                This operation is irreversible. Please confirm your password before you cancel this sale
                                                                                            </h4>
                                                                                            <br>
                                                                                            <!-- Hide This -->
                                                                                            <input type="hidden" name="sale_id" value="<?php echo $sales->sale_id; ?>">
                                                                                            <input type="hidden" name="product_id" value="<?php echo $sales->sale_product_id; ?>">
                                                                                            <input type="hidden" name="sale_quantity" value="<?php echo $sales->sale_quantity ?>">
                                                                                            <input type="hidden" name="sale_details" value="<?php echo $sales->product_code . ' ' . $sales->product_name; ?>">
                                                                                            <div class="form-group col-md-12">
                                                                                                <input type="password" required name="user_password" class="form-control">
                                                                                            </div>
                                                                                            <button type="button" class="text-center btn btn-success" data-dismiss="modal">No</button>
                                                                                            <input type="submit" name="cancel_sale" value="Cancel" class="text-center btn btn-danger">
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- End Modal -->
                                                                    </tr>
                                                                <?php
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php require_once('../partials/scripts.php'); ?>
</body>

</html>