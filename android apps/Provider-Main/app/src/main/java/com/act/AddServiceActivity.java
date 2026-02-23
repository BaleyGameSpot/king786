package com.act;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.Html;
import android.text.InputFilter;
import android.text.InputType;
import android.text.Spanned;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemSelectServiceDesignBinding;
import com.kyleduo.switchbutton.SwitchButton;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.AutoFitEditText;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Locale;

public class AddServiceActivity extends ParentActivity {

    private MTextView titleTxt, txtVideoConsultEnableTitle, txtRateHint, txtRateValue, txtVideoConsultServiceRequestMsg;
    private ImageView backImgView, ivEditVideoConsult;
    private String iVehicleCategoryId = "", vTitle = "", fAmount = "", fVideoConsultAmount = "";
    private final ArrayList<String> dataList = new ArrayList<>();
    private final ArrayList<MTextView> serviceAmtVTxt = new ArrayList<>();
    private MButton btn_type2;
    private int submitBtnId;
    private Dialog PriceEditConifrmAlertDialog, priceEditAlert;

    private ArrayList<Boolean> carTypesStatusArr;

    private ProgressBar loadingBar;
    private View contentView;
    private LinearLayout serviceSelectArea, llVideoConsultEnableView;
    private SwitchButton switchVideoConsult;
    private MaterialEditText editBoxDesc;

    //manage video consulte view
    View BottomllVideoConsultEnableView, topllVideoConsultEnableView, bottomBtnView;

    View bottomView;
    private boolean isCarSizeAddon, isCarService;
    private final String defaultAmountVal = AutoFitEditText.convertCommaToDecimal("0.00", false);


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_service);

        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        serviceSelectArea = findViewById(R.id.serviceSelectArea);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        loadingBar = findViewById(R.id.loadingBar);
        contentView = findViewById(R.id.contentView);


        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);

        Intent in = getIntent();
        iVehicleCategoryId = in.getStringExtra("iVehicleCategoryId");
        vTitle = in.getStringExtra("vTitle");

        // VideoConsultEnableView
        llVideoConsultEnableView = findViewById(R.id.llVideoConsultEnableView);
        txtVideoConsultServiceRequestMsg = findViewById(R.id.txtVideoConsultServiceRequestMsg);
        txtVideoConsultEnableTitle = findViewById(R.id.txtVideoConsultEnableTitle);
        switchVideoConsult = findViewById(R.id.switchVideoConsult);
        txtRateHint = findViewById(R.id.txtRateHint);
        txtRateValue = findViewById(R.id.txtRateValue);
        BottomllVideoConsultEnableView = findViewById(R.id.BottomllVideoConsultEnableView);
        topllVideoConsultEnableView = findViewById(R.id.topllVideoConsultEnableView);
        bottomBtnView = findViewById(R.id.bottomBtnView);

        editBoxDesc = findViewById(R.id.editBoxVideoConsultDescription);
        editBoxDesc.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        editBoxDesc.setSingleLine(false);
        editBoxDesc.setHideUnderline(true);
        editBoxDesc.setGravity(Gravity.START | Gravity.TOP);
        editBoxDesc.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        if (generalFunc.isRTLmode()) {
            editBoxDesc.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
            backImgView.setRotation(180);
        } else {
            editBoxDesc.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }
        editBoxDesc.setIncludeFontPadding(false);
        editBoxDesc.setHint(generalFunc.retrieveLangLBl("", "LBL_ADD_DESCRIPTION"));

        ivEditVideoConsult = findViewById(R.id.ivEditVideoConsult);
        ivEditVideoConsult.setOnClickListener(v -> videoConsultChangePriceDialog());

        setLabels();
        carTypesStatusArr = new ArrayList<>();
        getSubCategoryList();
    }

    private void manageVideoConculateView(boolean isVisible) {
        if (dataList.size() > 0) {
            BottomllVideoConsultEnableView.setVisibility(isVisible ? View.VISIBLE : View.GONE);
            manageBottomView();
        } else {
            topllVideoConsultEnableView.bringToFront();
            bottomBtnView.setBackground(null);
            topllVideoConsultEnableView.setVisibility(isVisible ? View.VISIBLE : View.GONE);
        }
    }

    private void manageBottomView() {
        bottomView = findViewById(R.id.bottomView);
        txtVideoConsultEnableTitle = bottomView.findViewById(R.id.txtVideoConsultEnableTitle);
        switchVideoConsult = bottomView.findViewById(R.id.switchVideoConsult);
        editBoxDesc = bottomView.findViewById(R.id.editBoxVideoConsultDescription);
        editBoxDesc.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        editBoxDesc.setSingleLine(false);
        editBoxDesc.setHideUnderline(true);
        editBoxDesc.setGravity(Gravity.START | Gravity.TOP);
        editBoxDesc.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        if (generalFunc.isRTLmode()) {
            editBoxDesc.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
            backImgView.setRotation(180);
        } else {
            editBoxDesc.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }
        editBoxDesc.setIncludeFontPadding(false);
        editBoxDesc.setHint(generalFunc.retrieveLangLBl("", "LBL_ADD_DESCRIPTION"));
        txtRateHint = bottomView.findViewById(R.id.txtRateHint);
        txtRateValue = bottomView.findViewById(R.id.txtRateValue);
        ivEditVideoConsult = bottomView.findViewById(R.id.ivEditVideoConsult);
        ivEditVideoConsult.setOnClickListener(v -> videoConsultChangePriceDialog());
        txtVideoConsultServiceRequestMsg = bottomBtnView.findViewById(R.id.txtVideoConsultServiceRequestMsg);
        setLabels();
    }

    private void getSubCategoryList() {

        loadingBar.setVisibility(View.VISIBLE);
        contentView.setVisibility(View.GONE);


        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceTypes");
        parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

                    if (responseStringObject != null && !responseStringObject.toString().equals("")) {

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {

                            isCarSizeAddon = generalFunc.getJsonValueStr("isCarSizeAddon", responseStringObject).equalsIgnoreCase("Yes");
                            isCarService = generalFunc.getJsonValueStr("isCarService", responseStringObject).equalsIgnoreCase("Yes");

                            String ENABLE_DRIVER_SERVICE_REQUEST_MODULE = generalFunc.getJsonValueStr("ENABLE_DRIVER_SERVICE_REQUEST_MODULE", responseStringObject);
                            JSONArray carList_arr = generalFunc.getJsonArray(Utils.message_str, responseStringObject);

                            if (carList_arr != null) {
                                for (int i = 0; i < carList_arr.length(); i++) {
                                    JSONObject obj = generalFunc.getJsonObject(carList_arr, i);
                                    dataList.add(obj.toString());
                                }
                            }
                            if (generalFunc.getJsonValueStr("eVideoConsultEnable", responseStringObject).equalsIgnoreCase("Yes")) {
                                manageVideoConculateView(true);
                                if (generalFunc.getJsonValueStr("eVideoConsultEnableProvider", responseStringObject).equalsIgnoreCase("Yes")) {
                                    switchVideoConsult.setChecked(true);
                                }
                                editBoxDesc.setText(generalFunc.getJsonValueStr("eVideoServiceDescription", responseStringObject));
                                txtRateValue.setText(generalFunc.getJsonValueStr("eVideoConsultServiceCharge", responseStringObject));
                                fVideoConsultAmount = generalFunc.getJsonValueStr("eVideoConsultServiceChargeAmount", responseStringObject);
                                if (generalFunc.getJsonValueStr("eServiceRequest", responseStringObject).equalsIgnoreCase("Pending")) {
                                    txtVideoConsultServiceRequestMsg.setVisibility(View.VISIBLE);
                                    txtVideoConsultServiceRequestMsg.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_REQUEST_PENDING"));
                                }
                                if (generalFunc.getJsonValueStr("ePriceType", responseStringObject).equalsIgnoreCase("Service")) {
                                    ivEditVideoConsult.setVisibility(View.GONE);
                                }
                            }
                            //buildServices();
                            buildServices(ENABLE_DRIVER_SERVICE_REQUEST_MODULE);
                        } else {

                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                generateAlert.closeAlertBox();

                                backImgView.performClick();
                            });
                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));

                            generateAlert.showAlertBox();
                        }
                    } else {
                        generalFunc.showError();
                    }

                    loadingBar.setVisibility(View.GONE);
                    contentView.setVisibility(View.VISIBLE);

                });

    }

    @SuppressLint("SetTextI18n")
    private void buildServices(String ENABLE_DRIVER_SERVICE_REQUEST_MODULE) {

        if (serviceSelectArea.getChildCount() > 0) {
            serviceSelectArea.removeAllViewsInLayout();
            serviceAmtVTxt.clear();
        }
        for (int i = 0; i < dataList.size(); i++) {
            String obj = dataList.get(i);

            final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemSelectServiceDesignBinding iBinding = ItemSelectServiceDesignBinding.inflate(inflater, serviceSelectArea, false);

            serviceAmtVTxt.add(i, iBinding.serviceamtVtxt);
            String[] vCarTypes = {};

            iBinding.serviceamtHtxt.setText(generalFunc.retrieveLangLBl("Rate", "LBL_RATE") + ": ");
            iBinding.serviceNameTxtView.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vTitle", obj)));
            String subTitle = generalFunc.getJsonValue("SubTitle", obj);
            if (Utils.checkText(subTitle)) {
                iBinding.serviceTypeNameTxtView.setText(subTitle);
                iBinding.serviceTypeNameTxtView.setVisibility(View.VISIBLE);
            } else {
                iBinding.serviceTypeNameTxtView.setVisibility(View.GONE);
            }

            String ischeck = generalFunc.getJsonValue("VehicleServiceStatus", obj);

            /*new service pending addon*/
            String eServiceStatus = generalFunc.getJsonValue("eServiceRequest", obj);

            if (ENABLE_DRIVER_SERVICE_REQUEST_MODULE.equalsIgnoreCase("Yes")) {

                if ((ischeck.equalsIgnoreCase("true") && eServiceStatus.equalsIgnoreCase("Active"))) {
                    iBinding.chkBox.setChecked(true);
                    iBinding.chkBox.setClickable(true);
                    carTypesStatusArr.add(true);

                } else if (ischeck.equalsIgnoreCase("false") && eServiceStatus.equalsIgnoreCase("Pending")) {
                    iBinding.chkBox.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_mark_gray));
                    iBinding.chkBox.setChecked(true);
                    iBinding.chkBox.setClickable(true);
                    carTypesStatusArr.add(false);

                } else if (ischeck.equalsIgnoreCase("false") && eServiceStatus.equalsIgnoreCase("Inactive")) {

                    iBinding.chkBox.setChecked(false);
                    iBinding.chkBox.setClickable(true);
                    carTypesStatusArr.add(false);
                }

            } else {

                if (ischeck.equalsIgnoreCase("true") || Arrays.asList(vCarTypes).contains(generalFunc.getJsonValue("iVehicleTypeId", obj))) {
                    iBinding.chkBox.setChecked(true);
                    carTypesStatusArr.add(true);
                } else {
                    carTypesStatusArr.add(false);
                }
            }
            /*end*/


            final int finalI = i;
            String eFareType = generalFunc.getJsonValue("eFareType", obj);
            if (generalFunc.getJsonValue("ePriceType", obj).equalsIgnoreCase("Provider") && (eFareType.equalsIgnoreCase("Fixed") || eFareType.equalsIgnoreCase("Hourly"))) {
                iBinding.editarea.setVisibility(View.VISIBLE);
                if (generalFunc.isRTLmode()) {
                    iBinding.editBtn.setScaleX(-1);
                }
                iBinding.editBtn.setVisibility(View.VISIBLE);
                iBinding.editBtnBack.setVisibility(View.VISIBLE);
            } else {
                iBinding.editarea.setVisibility(View.GONE);
                iBinding.editBtn.setVisibility(View.GONE);
                iBinding.editBtnBack.setVisibility(View.GONE);
            }

            if (eFareType != null && eFareType.trim().equals("Hourly") && generalFunc.getJsonValue("fMinHour", obj) != null && GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("fMinHour", obj)) > 1) {

                iBinding.minHourTxtView.setVisibility(View.VISIBLE);
                iBinding.minHourTxtView.setText("" + "(" + generalFunc.retrieveLangLBl("", "LBL_MINIMUM_TXT") + " " + generalFunc.getJsonValue("fMinHour", obj) + " " + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") + ")");
            } else {
                iBinding.minHourTxtView.setVisibility(View.GONE);
            }


            iBinding.container.setOnClickListener(v -> iBinding.chkBox.performClick());

            iBinding.chkBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                /*new service pending addon*/
                if (ENABLE_DRIVER_SERVICE_REQUEST_MODULE.equalsIgnoreCase("Yes")) {

                    if (!isChecked) {
                        if (eServiceStatus.equalsIgnoreCase("Pending")) {

                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                if (btn_id == 1) {
                                    carTypesStatusArr.set(finalI, false);
                                    generateAlert.closeAlertBox();
                                    iBinding.chkBox.setChecked(true);
                                }
                            });
                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_SERVICE_REQUEST_PENDING"));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                            generateAlert.showAlertBox();

                        } else if (eServiceStatus.equalsIgnoreCase("Active")) {

                            GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                if (btn_id == 0) {
                                    carTypesStatusArr.set(finalI, true);
                                    iBinding.chkBox.setChecked(true);
                                } else {
                                    carTypesStatusArr.set(finalI, false);
                                }
                            });
                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_UNSELECT_CHECKBOX_FOR_SERVICE"));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                            generateAlert.showAlertBox();

                        } else {
                            carTypesStatusArr.set(finalI, false);
                        }

                    } else {
                        carTypesStatusArr.set(finalI, true);
                    }

                } else {
                    carTypesStatusArr.set(finalI, isChecked);
                }
                /*end*/


            });
            iBinding.serviceamtVtxt.setText(getFAmountWithSymbol(obj));
            fAmount = generalFunc.getJsonValue("fAmount", obj);

            iBinding.editBtn.setOnClickListener(v -> driverChangePriceDilalg(finalI));

            //
            if (isCarSizeAddon && isCarService) {
                iBinding.chkBox.setVisibility(generalFunc.getJsonValue("IsShowCheckbox", obj).equalsIgnoreCase("No") ? View.GONE : View.VISIBLE);
                if (iBinding.editarea.getVisibility() == View.VISIBLE) {
                    iBinding.editarea.setVisibility(generalFunc.getJsonValue("IsShowPriceEditButton", obj).equalsIgnoreCase("No") ? View.GONE : View.VISIBLE);
                }
                if (iBinding.minHourTxtView.getVisibility() == View.VISIBLE) {
                    iBinding.minHourTxtView.setVisibility(generalFunc.getJsonValue("IsShowPriceEditButton", obj).equalsIgnoreCase("No") ? View.GONE : View.VISIBLE);
                }
                if (iBinding.editBtnBack.getVisibility() == View.VISIBLE) {
                    iBinding.editBtnBack.setVisibility(generalFunc.getJsonValue("IsShowPriceEditButton", obj).equalsIgnoreCase("No") ? View.GONE : View.VISIBLE);
                }

                iBinding.cardArea.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.card_view_23_white_line_flat_1));
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) iBinding.cardArea.getLayoutParams();
                if (generalFunc.getJsonValue("IsShowCheckbox", obj).equalsIgnoreCase("Yes")) {
                    if (i < (dataList.size() - 1) && generalFunc.getJsonValue("IsShowCheckbox", dataList.get(i + 1)).equalsIgnoreCase("Yes")) {
                        params.setMargins((int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._15sdp), 0);
                    } else {
                        params.setMargins((int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._minus7sdp));
                    }
                } else if (generalFunc.getJsonValue("IsShowCheckbox", obj).equalsIgnoreCase("No")) {
                    if (i < (dataList.size() - 1) && generalFunc.getJsonValue("IsShowCheckbox", dataList.get(i + 1)).equalsIgnoreCase("No")) {
                        params.setMargins((int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._minus7sdp), (int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._minus7sdp));
                    } else {
                        params.setMargins((int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._minus7sdp), (int) getResources().getDimension(R.dimen._15sdp), 0);
                    }
                }
                iBinding.cardArea.setLayoutParams(params);
            }

            serviceSelectArea.addView(iBinding.getRoot());
        }

        View tmpView = new View(getActContext());
        NestedScrollView.LayoutParams tmpLayoutParams = new NestedScrollView.LayoutParams(NestedScrollView.LayoutParams.MATCH_PARENT, Utils.dipToPixels(getActContext(), 15));

        tmpView.setLayoutParams(tmpLayoutParams);
        serviceSelectArea.addView(tmpView);

        if (dataList.isEmpty()) {
            serviceSelectArea.setVisibility(View.GONE);
        }
    }

    private Spanned getFAmountWithSymbol(String obj) {
        String fAmountWithSymbol = generalFunc.getJsonValue("fAmountWithSymbol", obj);
        String text1 = Utils.checkText(fAmountWithSymbol) ? fAmountWithSymbol : (generalFunc.getJsonValue("vCurrencySymbol", obj) + " " + generalFunc.getJsonValue("fAmount", obj));
        if (generalFunc.getJsonValue("eFareType", obj).equalsIgnoreCase("Hourly")) {
            String text2 = "/" + generalFunc.retrieveLangLBl("hour", "LBL_HOUR");
            return Html.fromHtml(text1 + "<font color='#1B2A3B'>" + text2 + "</font>");
        } else {
            return Html.fromHtml(text1);
        }
    }

    @SuppressLint("SetTextI18n")
    private void driverChangePriceDilalg(final int pos) {
        PriceEditConifrmAlertDialog = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        PriceEditConifrmAlertDialog.setContentView(R.layout.desgin_extracharge_confirm);
        final AutoFitEditText tipAmountEditBox = PriceEditConifrmAlertDialog.findViewById(R.id.editBox);
        tipAmountEditBox.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        tipAmountEditBox.setVisibility(View.VISIBLE);


        final MButton giveTipTxtArea = ((MaterialRippleLayout) PriceEditConifrmAlertDialog.findViewById(R.id.giveTipTxtArea)).getChildView();
        final MTextView skipTxtArea = PriceEditConifrmAlertDialog.findViewById(R.id.skipTxtArea);
        final MTextView titileTxt = PriceEditConifrmAlertDialog.findViewById(R.id.titileTxt);
        final MTextView msgTxt = PriceEditConifrmAlertDialog.findViewById(R.id.msgTxt);
        final MTextView addmoneynote = PriceEditConifrmAlertDialog.findViewById(R.id.addmoneynote);
        final MTextView CurrencySymbolTXT = PriceEditConifrmAlertDialog.findViewById(R.id.CurrencySymbolTXT);
        ImageView minusImageView = PriceEditConifrmAlertDialog.findViewById(R.id.minusImageView);
        ImageView addImageView = PriceEditConifrmAlertDialog.findViewById(R.id.addImageView);
        final MTextView lblserviceprice = PriceEditConifrmAlertDialog.findViewById(R.id.lblserviceprice);


        msgTxt.setVisibility(View.VISIBLE);
        titileTxt.setText(generalFunc.retrieveLangLBl("Enter Service Amount Below:", "LBL_ENTER_SERVICE_AMOUNT"));
        skipTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        lblserviceprice.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_PRICE"));
        msgTxt.setText("");

        msgTxt.setVisibility(View.GONE);
        giveTipTxtArea.setText("" + generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
        skipTxtArea.setText("" + generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        addmoneynote.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_MSG"));
        skipTxtArea.setOnClickListener(view -> {
            Utils.hideKeyboard(AddServiceActivity.this);
            PriceEditConifrmAlertDialog.dismiss();

        });
        tipAmountEditBox.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_AMOUNT"));
        String obj = dataList.get(pos);

        if (!generalFunc.getJsonValue("fAmount", obj).equals("") && generalFunc.getJsonValue("fAmount", obj) != null) {

            tipAmountEditBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("fAmount", obj))));
        } else {
            tipAmountEditBox.setText(defaultAmountVal);
        }


        addImageView.setOnClickListener(view -> mangePluseView(tipAmountEditBox));
        minusImageView.setOnClickListener(view -> mangeMinusView(tipAmountEditBox));
        CurrencySymbolTXT.setText(generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));
        tipAmountEditBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

        tipAmountEditBox.addTextChangedListener(giveTipTxtArea, false);

        giveTipTxtArea.setOnClickListener(view -> {
            Utils.hideKeyboard(AddServiceActivity.this);

            final boolean tipAmountEntered = Utils.checkText(tipAmountEditBox) || Utils.setErrorFields(tipAmountEditBox, generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));

            if (!tipAmountEntered) {
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) CurrencySymbolTXT.getLayoutParams();
                params.setMargins(0, 0, 0, 25);
                CurrencySymbolTXT.setLayoutParams(params);
                return;

            }
            if (GeneralFunctions.parseDoubleValue(0, tipAmountEditBox.getTxt()) > 0) {
                PriceEditConifrmAlertDialog.dismiss();

                try {
                    fAmount = tipAmountEditBox.getTxt();
                    addServiceAmount(pos);
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            } else {
                tipAmountEditBox.setText("");
                Utils.setErrorFields(tipAmountEditBox, generalFunc.retrieveLangLBl("", "LBL_ADD_CORRECT_DETAIL_TXT"));
            }
        });
        PriceEditConifrmAlertDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(PriceEditConifrmAlertDialog);


        Window window = PriceEditConifrmAlertDialog.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        PriceEditConifrmAlertDialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        PriceEditConifrmAlertDialog.show();

    }

    private void setLabels() {
        titleTxt.setText(vTitle);
        Logger.d("txtVideoConsultEnableTitle", "::" + generalFunc.retrieveLangLBl("", "LBL_AVAILBLE_VIDEO"));
        txtVideoConsultEnableTitle.setText(generalFunc.retrieveLangLBl("", "LBL_AVAILBLE_VIDEO"));
        txtRateHint.setText(generalFunc.retrieveLangLBl("", "LBL_RATE") + ": ");
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_UPDATE_SERVICES"));


    }

    private Context getActContext() {
        return AddServiceActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == submitBtnId) {
            String carTypes = "";
            for (int j = 0; j < carTypesStatusArr.size(); j++) {
                if (carTypesStatusArr.get(j)) {
                    String iVehicleTypeId = generalFunc.getJsonValue("iVehicleTypeId", dataList.get(j));
                    if (isCarSizeAddon && isCarService) {
                        if (generalFunc.getJsonValue("IsShowCheckbox", dataList.get(j)).equalsIgnoreCase("Yes")) {
                            carTypes = carTypes.equals("") ? iVehicleTypeId : (carTypes + "," + iVehicleTypeId);
                        }
                    } else {
                        carTypes = carTypes.equals("") ? iVehicleTypeId : (carTypes + "," + iVehicleTypeId);
                    }
                }
            }
            addService(carTypes);
        } else if (view == backImgView) {
            onBackPressed();
        }

    }

    @SuppressLint("SetTextI18n")
    private void addServiceAmount(int pos) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverServiceAmount");
        if (pos == -1) {
            parameters.put("fAmount", fVideoConsultAmount);
            parameters.put("isForVideoConsultant", "Yes");
            parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        } else {
            parameters.put("iVehicleTypeId", generalFunc.getJsonValue("iVehicleTypeId", dataList.get(pos)));
            parameters.put("iVehicleSizeId", generalFunc.getJsonValue("iVehicleSizeId", dataList.get(pos)));
            parameters.put("fAmount", fAmount);

        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {
                //dataList.clear();
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        //carTypesStatusArr.clear();
                        //getSubCategoryList();

                        if (pos == -1) {
                            txtRateValue.setText(generalFunc.getJsonValueStr("DisplayAmount", responseStringObject));
                        } else {

                            dataList.set(pos, generalFunc.getJsonValueStr(Utils.message_str_one, responseStringObject));

                            serviceAmtVTxt.get(pos).setText(getFAmountWithSymbol(dataList.get(pos)));
                        }
                    });

                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));

                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void addService(String vCarType) {

        if (llVideoConsultEnableView.getVisibility() == View.VISIBLE && switchVideoConsult.isChecked()) {
            boolean editBoxDesc1 = Utils.checkText(editBoxDesc) || Utils.setErrorFields(editBoxDesc, generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
            if (!editBoxDesc1) {
                return;
            }
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverVehicle");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCarType", vCarType);
        parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        parameters.put("eType", Utils.CabGeneralType_UberX);

        parameters.put("fAmount", fVideoConsultAmount);
        parameters.put("eVideoConsultEnableProvider", switchVideoConsult.isChecked() ? "Yes" : "No");
        parameters.put("eVideoServiceDescription", Utils.getText(editBoxDesc));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                if (isDataAvail) {
                    if (generalFunc.getJsonValueStr("isServiceDocAvail", responseStringObject).equalsIgnoreCase("Yes")) {
                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                generateAlert.closeAlertBox();
                                Intent returnIntent = new Intent();
                                returnIntent.putExtra("eVideoConsultEnableProvider", switchVideoConsult.isChecked() ? "Yes" : "No");
                                setResult(RESULT_OK, returnIntent);
                                backImgView.performClick();
                            } else if (btn_id == 1) {
                                Bundle bn = new Bundle();
                                bn.putString("PAGE_TYPE", "Driver");
                                bn.putString("iDriverVehicleId", "");
                                bn.putString("doc_file", "");
                                bn.putString("iDriverVehicleId", "");
                                new ActUtils(getApplicationContext()).startActWithDataNewTask(ListOfDocumentActivity.class, bn);
                            }
                        });
                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT"));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC"));
                        generateAlert.showAlertBox();
                        return;
                    }

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        Intent returnIntent = new Intent();
                        returnIntent.putExtra("eVideoConsultEnableProvider", switchVideoConsult.isChecked() ? "Yes" : "No");
                        setResult(RESULT_OK, returnIntent);
                        backImgView.performClick();
                    });

                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));

                    generateAlert.showAlertBox();

                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void mangeMinusView(AutoFitEditText rechargeBox) {
        if (Utils.checkText(rechargeBox) && GeneralFunctions.parseDoubleValue(0, rechargeBox.getTxt()) > 0) {
            rechargeBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt()) - 1));
        }
    }

    @SuppressLint("SetTextI18n")
    private void mangePluseView(AutoFitEditText rechargeBox) {
        if (Utils.checkText(rechargeBox)) {
            rechargeBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt()) + 1));
        } else {
            rechargeBox.setText("1.00");
        }
    }

    private void videoConsultChangePriceDialog() {
        if (priceEditAlert != null && priceEditAlert.isShowing()) {
            return;
        }
        priceEditAlert = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        priceEditAlert.setContentView(R.layout.desgin_extracharge_confirm);

        final AutoFitEditText tipAmountEditBox = priceEditAlert.findViewById(R.id.editBox);
        tipAmountEditBox.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        tipAmountEditBox.setVisibility(View.VISIBLE);

        final MButton giveTipTxtArea = ((MaterialRippleLayout) priceEditAlert.findViewById(R.id.giveTipTxtArea)).getChildView();
        final MTextView skipTxtArea = priceEditAlert.findViewById(R.id.skipTxtArea);
        final MTextView titileTxt = priceEditAlert.findViewById(R.id.titileTxt);
        final MTextView msgTxt = priceEditAlert.findViewById(R.id.msgTxt);
        final MTextView addmoneynote = priceEditAlert.findViewById(R.id.addmoneynote);
        final MTextView CurrencySymbolTXT = priceEditAlert.findViewById(R.id.CurrencySymbolTXT);
        ImageView minusImageView = priceEditAlert.findViewById(R.id.minusImageView);
        ImageView addImageView = priceEditAlert.findViewById(R.id.addImageView);
        final MTextView lblserviceprice = priceEditAlert.findViewById(R.id.lblserviceprice);

        msgTxt.setVisibility(View.VISIBLE);
        titileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_SERVICE_AMOUNT"));
        skipTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        lblserviceprice.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_PRICE"));
        msgTxt.setText("");

        msgTxt.setVisibility(View.GONE);
        giveTipTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
        skipTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        addmoneynote.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_MSG"));
        skipTxtArea.setOnClickListener(view -> {
            Utils.hideKeyboard(AddServiceActivity.this);
            priceEditAlert.dismiss();
        });

        tipAmountEditBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0, fVideoConsultAmount)));

        addImageView.setOnClickListener(view -> mangePluseView(tipAmountEditBox));
        minusImageView.setOnClickListener(view -> mangeMinusView(tipAmountEditBox));

        CurrencySymbolTXT.setText(generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));

        tipAmountEditBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        tipAmountEditBox.addTextChangedListener(giveTipTxtArea, false);

        giveTipTxtArea.setOnClickListener(view -> {
            Utils.hideKeyboard(AddServiceActivity.this);

            final boolean tipAmountEntered = Utils.checkText(tipAmountEditBox) || Utils.setErrorFields(tipAmountEditBox, generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));

            if (!tipAmountEntered) {
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) CurrencySymbolTXT.getLayoutParams();
                params.setMargins(0, 0, 0, 25);
                CurrencySymbolTXT.setLayoutParams(params);
                return;

            }
            if (GeneralFunctions.parseDoubleValue(0, tipAmountEditBox.getTxt()) > 0) {
                try {
                    priceEditAlert.dismiss();
                    fVideoConsultAmount = tipAmountEditBox.getTxt();
                    addServiceAmount(-1);
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            } else {
                tipAmountEditBox.setText("");
                Utils.setErrorFields(tipAmountEditBox, generalFunc.retrieveLangLBl("", "LBL_ADD_CORRECT_DETAIL_TXT"));
            }
        });
        priceEditAlert.setCancelable(false);
        LayoutDirection.setLayoutDirection(priceEditAlert);
        Window window = priceEditAlert.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        priceEditAlert.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        priceEditAlert.show();
    }
}