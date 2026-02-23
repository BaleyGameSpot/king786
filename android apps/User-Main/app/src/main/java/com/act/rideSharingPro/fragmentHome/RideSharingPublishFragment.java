package com.act.rideSharingPro.fragmentHome;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.widget.ViewPager2;

import com.act.ContactUsActivity;
import com.act.MyWalletActivity;
import com.act.PaymentWebviewActivity;
import com.act.rideSharingPro.RideMyList;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.fragment.RidePublishStep1Fragment;
import com.act.rideSharingPro.fragment.RidePublishStep1MultiStopFragment;
import com.act.rideSharingPro.fragment.RidePublishStep2Fragment;
import com.act.rideSharingPro.fragment.RidePublishStep3Fragment;
import com.act.rideSharingPro.fragment.RidePublishStep4Fragment;
import com.act.rideSharingPro.model.RideProPublishData;
import com.adapter.ViewPager2Adapter;
import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRidePublishBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideSharingPublishFragment extends BaseFragment {

    public ActivityRidePublishBinding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private ViewPager2Adapter mViewPager2Adapter;
    private final ArrayList<Fragment> listOfFrag = new ArrayList<>();

    public RideProPublishData mPublishData;
    public String vImage = "", cImageName = "";
    public boolean isUploadImageNew = false;
    private ServerTask currentCallExeWebServer;
    public String mDistance, mDuration;

    AlertDialog outstanding_dialog;
    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        mActivity = (RideSharingProHomeActivity) requireActivity();
        generalFunc = mActivity.generalFunc;
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        assert mActivity != null;
        if (mActivity.publishFrgView != null) {
            binding = mActivity.rsPublishFragment.binding;
            mPublishData = mActivity.rsPublishFragment.mPublishData;

            return mActivity.publishFrgView;
        }
        binding = DataBindingUtil.inflate(inflater, R.layout.activity_ride_publish, container, false);

        mPublishData = new RideProPublishData();

        initialization();
        mainDataSet();

        mActivity.publishFrgView = binding.getRoot();
        return mActivity.publishFrgView;
    }

    private void initialization() {
        assert mActivity != null;

        mActivity.setSupportActionBar(binding.toolbarInclude.toolbar);
        binding.toolbarInclude.backImgView.setVisibility(ServiceModule.OnlyRideSharingPro ? View.GONE : View.VISIBLE);
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PUBLISH_TXT"));

        binding.bottomAreaView.setVisibility(View.VISIBLE);
        binding.loading.setVisibility(View.GONE);

        binding.publishRideBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PUBLISH_BTN_TXT"));
        binding.publishRideBtn.setVisibility(View.GONE);
        addToClickHandler(binding.previousBtn);
        addToClickHandler(binding.nextBtn);
        addToClickHandler(binding.publishRideBtn);
        if (generalFunc.isRTLmode()) {
            binding.previousBtn.setRotation(0);
            binding.nextBtn.setRotation(180);
        }

        mViewPager2Adapter = new ViewPager2Adapter(mActivity.getSupportFragmentManager(), this.getLifecycle(), listOfFrag);
        binding.rideSharingStepViewPager.setAdapter(mViewPager2Adapter);
        binding.rideSharingStepViewPager.setUserInputEnabled(false);
        binding.rideSharingStepViewPager.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                super.onPageSelected(position);
                binding.headerHTxt.setVisibility(View.GONE);
                binding.shadowHeaderView.setVisibility(View.GONE);
                setPagerHeight();
            }
        });
    }

    @SuppressLint("NotifyDataSetChanged")
    private void mainDataSet() {
        listOfFrag.clear();

        listOfFrag.add(new RidePublishStep1Fragment());
        listOfFrag.add(new RidePublishStep1MultiStopFragment());
        listOfFrag.add(new RidePublishStep2Fragment());
        listOfFrag.add(new RidePublishStep3Fragment());
        listOfFrag.add(new RidePublishStep4Fragment());

        binding.rideSharingStepViewPager.setOffscreenPageLimit(listOfFrag.size());
        Objects.requireNonNull(binding.rideSharingStepViewPager.getAdapter()).notifyDataSetChanged();
        if (listOfFrag.size() >= 2) {
            binding.previousBtn.setVisibility(View.INVISIBLE);
            binding.nextBtn.setVisibility(View.VISIBLE);
            setToolSubTitle();
        }
    }

    public void setReturnRideFrag(ArrayList<RideProPublishData.MultiStopData> multiStopData) {
        new Handler().postDelayed(() -> {
            if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep1Fragment fragment) {
                fragment.setReturnRideFrag(multiStopData);
            }
        }, 500);
    }

    @SuppressLint("SetTextI18n")
    private void setToolSubTitle() {
        assert mActivity != null;

        int currItemPos = binding.rideSharingStepViewPager.getCurrentItem();
        if (generalFunc.isRTLmode()) {
            binding.StepHTxt.setText(listOfFrag.size() + "/" + (currItemPos + 1) + " " + generalFunc.retrieveLangLBl("", "LBL_STEP_TXT"));
        } else {
            binding.StepHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STEP_TXT") + " " + (currItemPos + 1) + "/" + listOfFrag.size() + " ");
        }
        if (currItemPos > 0) {
            binding.previousBtn.setVisibility(View.VISIBLE);
        } else {
            binding.previousBtn.setVisibility(View.INVISIBLE);
        }
        if ((currItemPos + 1) == listOfFrag.size()) {
            binding.publishRideBtn.setVisibility(View.VISIBLE);
            binding.nextBtn.setVisibility(View.GONE);
        } else {
            binding.publishRideBtn.setVisibility(View.GONE);
            binding.nextBtn.setVisibility(View.VISIBLE);
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void setPagePrevious() {
        Utils.hideKeyboard(mActivity);
        binding.rideSharingStepViewPager.setCurrentItem(binding.rideSharingStepViewPager.getCurrentItem() - 1, true);
        setToolSubTitle();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void setPageNext() {
        assert mActivity != null;
        Utils.hideKeyboard(mActivity);

        int currItemPos = binding.rideSharingStepViewPager.getCurrentItem();
        if (currItemPos == (listOfFrag.size() - 1)) {

            generalFunc.showMessage(binding.bottomAreaView, "Done");

        } else {
            binding.rideSharingStepViewPager.setCurrentItem(currItemPos + 1, true);
            setToolSubTitle();
        }
    }

    public void setPagerHeight() {
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            Fragment fragment = mViewPager2Adapter.createFragment(binding.rideSharingStepViewPager.getCurrentItem());
            View childView = fragment.getView();
            if (childView == null) return;

            int wMeasureSpec = View.MeasureSpec.makeMeasureSpec(childView.getWidth(), View.MeasureSpec.EXACTLY);
            int hMeasureSpec = View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED);
            childView.measure(wMeasureSpec, hMeasureSpec);

            LinearLayout.LayoutParams lyParams = (LinearLayout.LayoutParams) binding.rideSharingStepViewPager.getLayoutParams();
            if (lyParams.height != childView.getMeasuredHeight()) {
                lyParams.height = childView.getMeasuredHeight();
                binding.rideSharingStepViewPager.setLayoutParams(lyParams);
            }
        }, 200);
    }

    public void onClickView(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            if (mActivity != null) {
                mActivity.onBackPressed();
            }

        } else if (i == binding.previousBtn.getId()) {
            if (binding.previousBtn.getVisibility() == View.VISIBLE) {
                setPagePrevious();
            }

        } else if (i == binding.nextBtn.getId()) {
            if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep1Fragment fragment) {
                fragment.checkPageNext();
            } else if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep1MultiStopFragment fragment) {
                fragment.checkPageNext();
            } else if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep2Fragment fragment) {
                fragment.checkPageNext();
            } else if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep3Fragment fragment) {
                fragment.checkPageNext();
            }

        } else if (i == binding.publishRideBtn.getId()) {
            if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep4Fragment fragment) {
                fragment.checkPageNext();
            }
        }
    }

    public void onRecommendedPrice() {
        assert mActivity != null;

        if (!Utils.checkText(mDistance) || !Utils.checkText(mDuration)) {
            generalFunc.showMessage(binding.headerHTxt, generalFunc.retrieveLangLBl("", "LBL_REQUEST_FETCH_LOCATION_DETAILS"));
            return;
        }

        HashMap<String, Object> parameters = new HashMap<>();
        parameters.put("type", "GetRideShareRecommendedPrice");
        parameters.put("distance", mDistance);
        parameters.put("duration", mDuration);

        ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mPublishData.getMultiStopData();
        if (mMultiStopData != null) {
            JSONArray jaStore = new JSONArray();
            for (int j = 0; j < mMultiStopData.size(); j++) {
                RideProPublishData.MultiStopData data = mMultiStopData.get(j);
                try {
                    JSONObject stopOverPointsObj = new JSONObject();
                    stopOverPointsObj.put("add", data.getDestAddress());
                    stopOverPointsObj.put("lat", data.getDestLat());
                    stopOverPointsObj.put("long", "" + data.getDestLong());
                    jaStore.put(stopOverPointsObj);
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
            parameters.put("MSP", jaStore);
        }

        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }

        currentCallExeWebServer = ApiHandler.execute(mActivity, parameters, true, generalFunc, responseString -> {
            currentCallExeWebServer = null;
            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    mPublishData.setRecommendedPrice("" + generalFunc.getJsonValue("RecommdedPrice", responseString));
                    mPublishData.setPassengerNo("" + generalFunc.getJsonValue("PassengerNo", responseString));
                    mPublishData.setRecommdedPriceText("" + generalFunc.getJsonValue("RecommdedPriceText", responseString));
                    mPublishData.setRecommdedPriceRange("" + generalFunc.getJsonValue("RecommdedPriceRange", responseString));
                    mPublishData.setPointRecommendedPrice("" + generalFunc.getJsonValue("PointRecommendedPrice", responseString));
                    mPublishData.setCarDetails("" + generalFunc.getJsonValue("carDetails", responseString));
                    setPageNext();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (listOfFrag.get(binding.rideSharingStepViewPager.getCurrentItem()) instanceof RidePublishStep3Fragment fragment) {
            fragment.onFileSelected(mFileUri, mFilePath, mFileType);
        }
    }

    public void handleImgUploadResponse(String responseString) {
        String msg;
        assert mActivity != null;
        if (responseString != null && !responseString.equalsIgnoreCase("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                CustomDialog customDialog = new CustomDialog(mActivity, mActivity.generalFunc);
                if (!generalFunc.getJsonValue("messageReviewDoc", responseString).equalsIgnoreCase("") && Utils.checkText(generalFunc.getJsonValue("messageReviewDoc", responseString))) {
                    msg = generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("messageReviewDoc", responseString)) + "\n" + "\n" + generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString));
                } else {
                    msg = generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString));
                }
                customDialog.setDetails(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)),
                        msg,
                        generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_VIEW_MY_RIDES_TXT"),
                        generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"),
                        false, R.drawable.ic_correct_2, false, 1, true);
                customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
                customDialog.setRoundedViewBorderColor(R.color.white);
                customDialog.setImgStrokWidth(15);
                customDialog.setBtnRadius(10);
                customDialog.setIconTintColor(R.color.white);
                customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
                customDialog.setPositiveBtnTextColor(R.color.white);
                customDialog.createDialog();
                customDialog.setPositiveButtonClick(() -> {
                    if (!ServiceModule.OnlyRideSharingPro) {
                        Bundle bn = new Bundle();
                        bn.putBoolean("isRestartApp", true);
                        new ActUtils(mActivity).startActWithData(RideMyList.class, bn);
                    } else {
                        mActivity.setFrag(0);
                    }
                });
                customDialog.setNegativeButtonClick(() -> MyApp.getInstance().restartWithGetDataApp());
                customDialog.setFullButton(generalFunc.retrieveLangLBl("", "LBL_RETUEN_RIDE_RIDE_SHARE_TEXT"), () -> {
                    mActivity.setReturnRideFrag(mPublishData.getMultiStopData());
                }, "", null);
                customDialog.show();
            } else {
                String fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", responseString);
                if (GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0) {
                    outstandingDialog(responseString);
                    return;
                }

                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), generalFunc.retrieveLangLBl("", "LBL_ADD_NOW"),
                        generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> {
                            if (buttonId == 0) {
                                new ActUtils(mActivity).startAct(MyWalletActivity.class);
                            }
                        });
            }
        } else {
            generalFunc.showError();
        }
    }

    public void sendToPublishRide() {

        ArrayList<RideProPublishData.MultiStopData> multiStopData = mPublishData.getMultiStopData();
        if (multiStopData == null || binding.loading.getVisibility() == View.VISIBLE) {
            return;
        }

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "PublishRide");

            put("tStartCity", mPublishData.getStartCity());
            put("tEndCity", mPublishData.getEndCity());

            // Step1 Data
            put("tStartLat", "" + multiStopData.get(0).getDestLat());
            put("tStartLong", "" + multiStopData.get(0).getDestLong());
            put("tEndLat", "" + multiStopData.get(multiStopData.size() - 1).getDestLat());
            put("tEndLong", "" + multiStopData.get(multiStopData.size() - 1).getDestLong());

            put("tSAddress", "" + multiStopData.get(0).getDestAddress());
            put("tDAddress", "" + multiStopData.get(multiStopData.size() - 1).getDestAddress());

            // Step2 Data
            put("dStartDate", mPublishData.getDateTime());
            put("iAvailableSeats", mPublishData.getPerSeat());
            put("fPrice", mPublishData.getRecommendedPrice());

            // step3Data
            put("tDriverDetails", mPublishData.getDynamicDetailsArray() != null ? mPublishData.getDynamicDetailsArray().toString() : "");

            // step4Data
            if (mPublishData.getDocumentIds() != null && !mPublishData.getDocumentIds().isEmpty()) {
                put("documentIds", mPublishData.getDocumentIds());
            }
            put("PointRecommendedPrice", mPublishData.getPointRecommendedPrice());
        }};

        UploadProfileImage uploadProfileImage;
        if (Utils.checkText(vImage)) {

            if (isUploadImageNew) {
                uploadProfileImage = new UploadProfileImage(true, mActivity, vImage, "TempFile." + Utils.getFileExt(vImage), paramsList);
            } else {
                paramsList.put("existingCarImageName", cImageName);
                uploadProfileImage = new UploadProfileImage(true, mActivity, "", "TempFile." + Utils.getFileExt(vImage), paramsList);
            }
        } else {
            uploadProfileImage = new UploadProfileImage(true, mActivity, vImage, "TempFile." + Utils.getFileExt(vImage), paramsList);
        }
        uploadProfileImage.execute();
    }

    public void outstandingDialog(String data) {
        AlertDialog.Builder builder = new AlertDialog.Builder(mActivity);
        LayoutInflater inflater = (LayoutInflater) mActivity.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);
        final MTextView outStandingTitle = (MTextView) dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = (MTextView) dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = (MTextView) dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = (MTextView) dialogView.findViewById(R.id.adjustTitleTxt);
        final LinearLayout cardArea = (LinearLayout) dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = (LinearLayout) dialogView.findViewById(R.id.adjustarea);
        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }
        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));
        outStandingValue.setText(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", data));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));
        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_OUT_AMT_DELIVERY_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        String outstanding_amt_pay_label = generalFunc.getJsonValue("outstanding_amt_pay_label", data);
        String outstanding_restriction_label_card = generalFunc.getJsonValue("outstanding_restriction_label_card", data);
        String outstanding_restriction_label_cash = generalFunc.getJsonValue("outstanding_restriction_label_cash", data);

        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", data);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", data);
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", data);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;

        //ShowAdjustTripBtn = "No";

        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        }

        if (outstanding_amt_pay_label != null && !outstanding_amt_pay_label.isEmpty()) {
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            adjustTripMessageTxt.setText(outstanding_amt_pay_label);
        }

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", mActivity.obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(mActivity).startAct(ContactUsActivity.class));
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", mActivity.obj_userProfile) + "&eType=" + "RideShare" + "&ePaymentType=ChargeOutstandingAmount";
            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

            Intent intent = new Intent(mActivity, PaymentWebviewActivity.class);
            Bundle bn = new Bundle();
            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            intent.putExtras(bn);
            webViewPaymentActivity.launch(intent);
        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            sendToPublishRide();
        });

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mActivity, R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        btn_type2.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
        });

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(mActivity, R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    ActivityResultLauncher<Intent> webViewPaymentActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                if (result.getResultCode() == Activity.RESULT_OK) {
                    //showRentItemPostDoneAlert();
                }
            });
}