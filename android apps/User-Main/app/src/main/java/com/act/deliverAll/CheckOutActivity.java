package com.act.deliverAll;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Rect;
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextUtils;
import android.text.method.ScrollingMovementMethod;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewTreeObserver;
import android.view.Window;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.RelativeLayout;
import android.widget.ScrollView;
import android.widget.SeekBar;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.RecyclerView;

import com.AudioRecord.AudioListener;
import com.AudioRecord.AudioRecordButton;
import com.AudioRecord.AudioRecording;
import com.AudioRecord.RecordingItem;
import com.act.AccountverificationActivity;
import com.act.AddAddressActivity;
import com.act.ContactUsActivity;
import com.act.CouponActivity;
import com.act.ListAddressActivity;
import com.act.MyWalletActivity;
import com.act.PaymentWebviewActivity;
import com.act.PrescriptionActivity;
import com.act.VerifyInfoActivity;
import com.activity.ParentActivity;
import com.adapter.files.MoreInstructionAdapter;
import com.general.files.ActUtils;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.SetGeneralData;
import com.general.files.UploadProfileImage;
import com.google.android.material.snackbar.Snackbar;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemCheckoutRowBinding;
import com.model.getProfilePaymentModel;
import com.model.profileDelegate;
import com.realmModel.Cart;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.WKWebView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.text.DecimalFormat;
import java.text.NumberFormat;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import io.realm.Realm;
import io.realm.RealmResults;

public class CheckOutActivity extends ParentActivity implements profileDelegate {

    private static final int RC_SIGN_IN_UP = 007;
    private static final int ADD_ADDRESS = 006;
    private static final int SEL_ADDRESS = 005;
    ImageView backImgView;
    MTextView titleTxt;

    MButton btn_type2, btn_clearcart;
    int submitBtnId;
    double finalTotal = 0;
    RealmResults<Cart> realmCartList;
    ArrayList<Cart> cartList;
    LinearLayout itemContainer;
    MTextView restaurantNameTxtView;
    String currencySymbol = "";
    MTextView applyCouponHTxt, applyCouponVTxt, chargesHTxt;
    MTextView appliedPromoHTxtView;
    ImageView couponCodeImgView, couponCodeCloseImgView;
    View couponCodeArea;
    boolean isLogin = false;
    MTextView noLoginNoteTxt;

    MTextView addressTxt;
    ImageView editLocation;
    View delveryAddressArea;
    String userProfileJson;
    MTextView paymentModeTitle;

    String selAddress = "";
    String selLatitude = "";
    String selLongitude = "";
    String selAddressId = "";

    MaterialEditText deliveryInstructionBox;
    MTextView deliverinstruction;
    String LBL_MINIMUM_ORDER_NOTE = "";
    String CurrencySymbol = "";

    LinearLayout promocodeArea;
    MTextView promocodeappliedHTxt;
    MTextView promocodeappliedVTxt;
    String ePaymentOption = "Cash";
    ErrorView errorView;
    InternetConnection internetConnection;
    ScrollView contentArea;
    NestedScrollView nestedScroll;
    LinearLayout bottomArea;
    String iOrderId = "";
    LinearLayout farecontainer;
    String restaurantstatus = "";
    String ToTalAddress = "0";
    boolean isselectaddress = false;
    boolean prevselectaddress = false;
    int totalqtycnt = 0;
    LinearLayout maxItemarea, btn_type2area;
    MTextView maxitemTitleTxtView, maxitemmsgTxtView;
    ArrayList<HashMap<String, String>> itemArraylist = new ArrayList<HashMap<String, String>>();
    AppCompatCheckBox checkboxWallet;
    String isWalletSelect = "No";
    MTextView walletAmountTxt;
    boolean isFromEditCard = false;
    boolean iswalletZero = false;

    private String appliedPromoCode = "";
    public String eWalletIgnore = "No";
    String SITE_TYPE = "";

    private String APP_PAYMENT_MODE = "";
    boolean isCODAllow = true;
    ImageView rightImgView;
    LinearLayout locationArea, EditAddressArea, AddAddressArea;
    MTextView addAddressHTxt, addAddressBtn, selLocTxt, resAddressTxtView;
    ImageView editCartImageview, storeImg;
    String outStandingAmount = "";

    LinearLayout deliveryTypeArea;
    MTextView tvDeliveryTypeTxt;
    RadioGroup takeAwayRadioGroups;
    RadioButton takeAwayRadio, deliverToDoorRadio;
    String eTakeAway = "No";

    /*Tip Feature View Declarion Start*/
    LinearLayout tipAmountArea1, tipAmountArea2, tipAmountArea3, tipAmountAreaOther;
    ImageView closeImg1, closeImg2, closeImg3, closeImgOther;
    MTextView tipAmountText1, tipAmountText2, tipAmountText3, tipAmountOtherText;
    MTextView tipTitleText;
    ImageView iv_tip_help;
    EditText tipAmountBox;
    MTextView errorTxt;
    LinearLayout addTipArea;
    LinearLayout amountArea;
    //    MButton tipGivenBtn;
    SelectableRoundedImageView tipGivenBtn;
    String tipAmount = "";
    LinearLayout tipCardArea;
    private boolean isPercentage;
    /*Tip Feature View Declarion Start*/


    RecyclerView moreinstuction;

    MoreInstructionAdapter moreInstructionAdapter;
    LinearLayout moreinstructionLyout;
    ArrayList<HashMap<String, String>> instructionslit;
    MTextView textmoreinstruction;
    MTextView textVoiceinstruction, textVoiceTitle, textVoicesubTitle, timeTxt;
    MTextView textrecordTitle, textrecordsubTitle;
    LinearLayout playArea, miscArea, miscNoteArea;
    ImageView playBtn, dltBtn, miscImg;
    AudioRecordButton audio_record_button;
    RecordingItem recordingItem1 = null;
    AudioRecording audioRecording;
    public SeekBar seekbar;
    boolean wasPlaying = false;
    private boolean intialStage = true;
    boolean isPause = false;
    ImageView voiceHelp;
    boolean isPreference = false;
    private List<HashMap<String, String>> currentSelectedItems = new ArrayList<>();
    ImageView PreferenceHelp;
    LinearLayout headerArea;
    LinearLayout delveryVoiceInstArea;
    RelativeLayout detailsArea;
    ProgressBar loading;

    //manage proof
    String eShowTerms;
    String eProofUpload;
    String tProofNote;
    Dialog uploadServicePicAlertBox = null;
    private String selectedImagePath = "";
    String iIdProofImageId = "";

    ImageView help_deliverto_door, help_takeaway;

    String iVoiceDirectionFileId = "";
    RelativeLayout rootLayout;

    //new flow for card
    MTextView payTypeTxt, organizationTxt;
    ImageView payImgView, errorImage;
    View showDropDownArea;
    LinearLayout payArea;
    String lastOrderPaymentMethod = "";
    private static final int WEBVIEWPAYMENT = 001;
    boolean isContectLessPrefSelected = false;

    //manage Outstanding.
    private AlertDialog outstanding_dialog;
    private boolean isAdjustPress;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_check_out);
        initViews();
        //getProfilePaymentModel.getProfilePayment(Utils.eSystem_Type, this, this, false,false);
        managePaymentOptions();
        manageProfilePayment();
        getLocalData();
        internetConnection = new InternetConnection(getActContext());
        if (internetConnection.isNetworkConnected()) {
            CheckOutOrderEstimateDetails("", false);
        } else {
            generateErrorView();
        }
    }

    private void getLocalData() {
        try {
            finalTotal = 0;
            realmCartList = getCartData();
            if (realmCartList.size() > 0) {
                cartList = new ArrayList<>(realmCartList);
                setData();
            }
        } catch (Exception e) {
            Logger.e("ExceptionFrag", "::" + e.getMessage());
        }
    }

    private RealmResults<Cart> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Cart.class).findAll();
    }

    private void setData() {
        restaurantNameTxtView.setText(cartList.get(0).getvCompany());
        //addItemView();
    }

    @SuppressLint("SetTextI18n")
    private void addItemView() {
        totalqtycnt = 0;

        if (itemContainer.getChildCount() > 0) {
            itemContainer.removeAllViewsInLayout();
        }
        LayoutInflater itemCartInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        for (int i = 0; i < itemArraylist.size(); i++) {
            @NonNull ItemCheckoutRowBinding binding = ItemCheckoutRowBinding.inflate(itemCartInflater, itemContainer, false);

            binding.itemNameTxtView.setSelected(true);
            binding.itemMenuNameTxtView.setSelected(true);

            binding.cancelImg.setTag(i);

            binding.cancelImg.setOnClickListener(view -> {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CONFIRM_MSG"));
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                generateAlert.setBtnClickList(btn_id -> {
                    generateAlert.closeAlertBox();
                    if (btn_id == 1) {
                        Realm realm = MyApp.getRealmInstance();
                        realm.beginTransaction();
                        Cart cart = realmCartList.get((Integer) binding.cancelImg.getTag());
                        if (cart != null) {
                            cart.deleteFromRealm();
                        }
                        realm.commitTransaction();

                        realmCartList = getCartData();
                        if (realmCartList.isEmpty()) {
                            onBackPressed();
                        }
                        CheckOutOrderEstimateDetails(appliedPromoCode, false);
                    }
                });
                generateAlert.showAlertBox();
            });

            HashMap<String, String> itemData = itemArraylist.get(i);
            String fOfferAmt = itemData.get("fOfferAmt");
            double fOfferAmtVal = GeneralFunctions.parseDoubleValue(0, fOfferAmt != null ? fOfferAmt : "");

            if (fOfferAmtVal > 0) {
                binding.itemPriceTxtView.setText(generalFunc.convertNumberWithRTL(itemData.get("fPrice")));

                binding.itemstrikePriceTxtView.setVisibility(View.VISIBLE);
                binding.itemstrikePriceTxtView.setPaintFlags(binding.itemstrikePriceTxtView.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
                binding.itemstrikePriceTxtView.setText(generalFunc.convertNumberWithRTL(itemData.get("fOriginalPrice")));
            } else {
                binding.itemstrikePriceTxtView.setVisibility(View.GONE);
                binding.itemPriceTxtView.setText(generalFunc.convertNumberWithRTL(itemData.get("fPrice")));
            }

            int sWidth = getResources().getDimensionPixelSize(R.dimen._68sdp);
            int sHeight = (int) (sWidth / 1.33333333333);

            LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) binding.menuImage.getLayoutParams();
            mParams.width = sWidth;
            mParams.height = sHeight;
            binding.menuImage.setLayoutParams(mParams);

            String Url = itemData.get("vImage");
            if (!Utils.checkText(Url)) {
                Url = "Temp";
            }
            Url = Utils.getResizeImgURL(getActContext(), Url, sWidth, sHeight);
            new LoadImage.builder(LoadImage.bind(Url), binding.menuImage).setPlaceholderImagePath(R.color.imageBg).setErrorImagePath(R.color.imageBg).build();

            binding.itemNameTxtView.setText(itemData.get("vItemType"));

            String optionaddonname = itemData.get("optionaddonname");
            if (Utils.checkText(optionaddonname)) {
                binding.itemMenuNameTxtView.setVisibility(View.VISIBLE);
                binding.itemMenuNameTxtView.setText("(" + optionaddonname + ")");
            } else {
                binding.itemMenuNameTxtView.setVisibility(View.GONE);
            }
            binding.QTYNumberTxtView.setText("x" + generalFunc.convertNumberWithRTL(itemData.get("iQty")));
            if (cartList != null && Utils.checkText(cartList.get(i).getQty())) {
                totalqtycnt = totalqtycnt + GeneralFunctions.parseIntegerValue(0, cartList.get(i).getQty());
            }
            itemContainer.addView(binding.getRoot());
        }
        manageMaxQtyArea();
        setButtonTxt();
    }

    private void manageMaxQtyArea() {
        int maxqty = GeneralFunctions.parseIntegerValue(0, generalFunc.retrieveValue(Utils.COMPANY_MAX_QTY));
        if (maxqty != 0) {
            if (totalqtycnt > maxqty) {
                maxItemarea.setVisibility(View.VISIBLE);
                btn_type2area.setVisibility(View.GONE);
            } else {
                maxItemarea.setVisibility(View.GONE);
                btn_type2area.setVisibility(View.VISIBLE);
            }
        }
    }

    private void setButtonTxt() {
        if (generalFunc.getMemberId() != null && !generalFunc.getMemberId().equalsIgnoreCase("")) {
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_TXT"));

            if (eProofUpload.equalsIgnoreCase("Yes")) {
                btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
            }
        }
    }

    private Context getActContext() {
        return CheckOutActivity.this;
    }

    private void initViews() {
        payTypeTxt = findViewById(R.id.payTypeTxt);
        organizationTxt = findViewById(R.id.organizationTxt);
        payImgView = findViewById(R.id.payImgView);
        errorImage = findViewById(R.id.errorImage);
        showDropDownArea = findViewById(R.id.showDropDownArea);
        /*Tip Feature Views casting*/
        tipCardArea = (LinearLayout) findViewById(R.id.tipCardArea);
        tipAmountOtherText = (MTextView) findViewById(R.id.tipAmountTextOther);
        tipAmountAreaOther = (LinearLayout) findViewById(R.id.tipAmountAreaOther);
        closeImgOther = (ImageView) findViewById(R.id.closeImgOther);

        tipAmountText1 = (MTextView) findViewById(R.id.tipAmountText1);
        tipAmountArea1 = (LinearLayout) findViewById(R.id.tipAmountArea1);
        closeImg1 = (ImageView) findViewById(R.id.closeImg1);

        tipAmountText2 = (MTextView) findViewById(R.id.tipAmountText2);
        tipAmountArea2 = (LinearLayout) findViewById(R.id.tipAmountArea2);
        closeImg2 = (ImageView) findViewById(R.id.closeImg2);

        tipAmountText3 = (MTextView) findViewById(R.id.tipAmountText3);
        tipAmountArea3 = (LinearLayout) findViewById(R.id.tipAmountArea3);
        closeImg3 = (ImageView) findViewById(R.id.closeImg3);


        addToClickHandler(tipAmountArea1);
        addToClickHandler(closeImg1);
        addToClickHandler(tipAmountArea2);
        addToClickHandler(closeImg2);
        addToClickHandler(tipAmountArea3);
        addToClickHandler(closeImg3);
        addToClickHandler(tipAmountAreaOther);
        addToClickHandler(closeImgOther);


        tipTitleText = (MTextView) findViewById(R.id.tipTitleText);
        iv_tip_help = (ImageView) findViewById(R.id.iv_tip_help);
        addToClickHandler(iv_tip_help);
        tipAmountBox = (EditText) findViewById(R.id.tipAmountBox);
        errorTxt = (MTextView) findViewById(R.id.errorTxt);
        addTipArea = (LinearLayout) findViewById(R.id.addTipArea);
        amountArea = (LinearLayout) findViewById(R.id.amountArea);

//        tipGivenBtn = ((MaterialRippleLayout) findViewById(R.id.tipGivenBtn)).getChildView();
        tipGivenBtn = (SelectableRoundedImageView) findViewById(R.id.tipGivenBtn);
        addToClickHandler(tipGivenBtn);
//        giveTipId = Utils.generateViewId();
//        tipGivenBtn.setId(giveTipId);

        new CreateRoundedView(getResources().getColor(R.color.appThemeColor_1), Utils.dipToPixels(getActContext(), getResources().getDimension(R.dimen._30sdp)), 5,
                getResources().getColor(R.color.gray_holo_dark), tipGivenBtn);
//        tipGivenBtn.setColorFilter(getResources().getColor(R.color.appThemeColor_TXT_1));

        tipAmountBox.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        tipAmountBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

        tipTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_TITLE_TXT"));
        tipAmountBox.setHint(generalFunc.retrieveLangLBl("", "LBL_TIP_AMOUNT_ENTER_TITLE"));
//        tipGivenBtn.setText(generalFunc.retrieveLangLBl("","LBL_BTN_SUBMIT_TXT"));

        tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));

        /*Tip Feature Views casting*/

        rootLayout = findViewById(R.id.rootLayout);
        rootLayout.getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
            @Override
            public void onGlobalLayout() {
                Rect r = new Rect();
                rootLayout.getWindowVisibleDisplayFrame(r);
                int screenHeight = rootLayout.getRootView().getHeight();
                int keypadHeight = screenHeight - r.bottom;
                if (keypadHeight > screenHeight * 0.15) {
                    bottomArea.setVisibility(View.GONE);
                    locationArea.setVisibility(View.GONE);
                } else {
                    bottomArea.setVisibility(View.VISIBLE);
                    locationArea.setVisibility(View.VISIBLE);
                }
            }
        });

        getUserProfileJson();

        /*Tip Feature- Tip Area Show Setting*/
        String ENABLE_TIP_MODULE_DELIVERALL_ = generalFunc.getJsonValue("ENABLE_TIP_MODULE_DELIVERALL", userProfileJson);
        if (!generalFunc.getMemberId().equals("") && ENABLE_TIP_MODULE_DELIVERALL_.equalsIgnoreCase("Yes") && eTakeAway.equalsIgnoreCase("No")) {
            tipCardArea.setVisibility(View.VISIBLE);
        } else {
            tipCardArea.setVisibility(View.GONE);
        }

        SITE_TYPE = generalFunc.getJsonValue("SITE_TYPE", userProfileJson);
        headerArea = (LinearLayout) findViewById(R.id.headerArea);
        delveryVoiceInstArea = (LinearLayout) findViewById(R.id.delveryVoiceInstArea);
        textVoiceinstruction = findViewById(R.id.textVoiceinstruction);
        textVoiceTitle = findViewById(R.id.textVoiceTitle);
        textVoicesubTitle = findViewById(R.id.textVoicesubTitle);
        textrecordTitle = findViewById(R.id.textrecordTitle);
        textrecordsubTitle = findViewById(R.id.textrecordsubTitle);
        timeTxt = findViewById(R.id.timeTxt);
        playArea = findViewById(R.id.playArea);
        playBtn = findViewById(R.id.playBtn);
        dltBtn = findViewById(R.id.dltBtn);
        miscImg = findViewById(R.id.miscImg);
        audioRecording = new AudioRecording(getActContext());
        audio_record_button = findViewById(R.id.audio_record_button);


        seekbar = findViewById(R.id.seekbar);
        miscArea = findViewById(R.id.miscArea);
        miscNoteArea = findViewById(R.id.miscNoteArea);
        textVoiceinstruction.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_INS"));
        textVoiceTitle.setText(generalFunc.retrieveLangLBl("", "LBL_VOICE_DIRECTIONS"));
        textVoicesubTitle.setText(generalFunc.retrieveLangLBl("", "LBL_VOICE_DETAILS"));
        textrecordTitle.setText(generalFunc.retrieveLangLBl("", "LBL_TAP_HOLD_RECORD"));
        textrecordsubTitle.setText(generalFunc.retrieveLangLBl("", "LBL_KEEP_RECORDING_TIME"));
        if (generalFunc.getJsonValue("ENABLE_DELIVERY_INSTRUCTIONS_ORDERS", userProfileJson) != null &&
                generalFunc.getJsonValue("ENABLE_DELIVERY_INSTRUCTIONS_ORDERS", userProfileJson).equalsIgnoreCase(
                        "Yes")) {
            delveryVoiceInstArea.setVisibility(View.VISIBLE);
        } else {
            delveryVoiceInstArea.setVisibility(View.GONE);

        }
        voiceHelp = findViewById(R.id.voiceHelp);
        voiceHelp.setOnClickListener(view -> showPreferenceHelp(getResources().getDrawable(R.drawable.ic_microphone_help), "LBL_DELIVERY_INS_NOTE", generalFunc.retrieveLangLBl("", "LBL_DELIVERY_INS")));

        audio_record_button.setOnAudioListener(new AudioListener() {
            @Override
            public void onStop(RecordingItem recordingItem) {
                recordingItem1 = recordingItem;

                try {
                    Logger.d("audio_record_button", "onStop");
                    long milliseconds = recordingItem.getLength();

                    long minutes = (milliseconds / 1000) / 60;
                    long seconds = (milliseconds / 1000) % 60;
                    Logger.d("Timer", "::" + minutes + "::" + seconds);
                    NumberFormat f = new DecimalFormat("00");
                    timeTxt.setText(f.format(minutes) + ":" + f.format(seconds));
                    miscArea.setVisibility(View.GONE);
                    playArea.setVisibility(View.VISIBLE);

                } catch (Exception e) {

                }
            }

            @Override
            public void onCancel() {
                Logger.d("audio_record_button", "onCancel");
                dltBtn.performClick();
            }

            @Override
            public void onError(Exception e) {
                Logger.d("audio_record_button", "onError");

            }
        });
        dltBtn.setOnClickListener(view -> {
            playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));
            isPause = false;
            intialStage = true;
            recordingItem1 = null;
            if (audioRecording.mMediaPlayer != null) {
                audioRecording.mMediaPlayer.stop();
            }
            miscArea.setVisibility(View.VISIBLE);
            playArea.setVisibility(View.GONE);
            audioRecording.mMediaPlayer = null;
        });
        playBtn.setOnClickListener(view -> {
            try {

                if (audioRecording.mMediaPlayer != null && audioRecording.mMediaPlayer.isPlaying()) {
                    // clearMediaPlayer();
                    // seekbar.setProgress(0);
                    isPause = true;
                    pauseMediaPlayer();
                    Log.d("wasPlaying", "::00::" + wasPlaying);
                    playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));
                    return;
                }
            } catch (Exception e) {
                Logger.d("Exception", "::" + e.toString());
            }

            if (isPause && audioRecording.mMediaPlayer != null) {
                audioRecording.mMediaPlayer.start();
                audioRecording.play();
                playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_pause_24));
                isPause = false;

            } else if (!wasPlaying) {
                Log.d("wasPlaying", "::11::" + wasPlaying);
                if (intialStage) {
                    intialStage = false;
                    playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_pause_24));

                    seekbar.setProgress(0);
                    audioRecording.play(recordingItem1);
                } else {
                    playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_pause_24));

                    try {
                        audioRecording.mMediaPlayer.prepare();
                    } catch (IOException e) {
                        Logger.d("intialStage", "::" + e.toString());
                    } catch (IllegalStateException e) {
                        Logger.d("intialStage", "::" + e.toString());
                    } catch (Exception e) {
                        Logger.d("intialStage", "::" + e.toString());
                    }

                    try {
                        audioRecording.mMediaPlayer.start();
                    } catch (IllegalStateException e) {
                        Logger.d("intialStage", "::" + e.toString());
                    } catch (Exception e) {
                        Logger.d("intialStage", "::" + e.toString());
                    }
                    audioRecording.pauseplay();

                }

            }
            wasPlaying = false;


            seekbar.setOnSeekBarChangeListener(new SeekBar.OnSeekBarChangeListener() {
                @Override
                public void onStartTrackingTouch(SeekBar seekBar) {


                }

                @Override
                public void onProgressChanged(SeekBar seekBar, int progress, boolean fromTouch) {

                    if (audioRecording.mMediaPlayer == null) {
                        seekBar.setProgress(0);
                        return;
                    }

                    int x = (int) Math.ceil(progress / 1000f);

                    Log.d("Progress", "::" + progress);

                    if (x < 10) {
                        timeTxt.setText("00:0" + x);
                    } else if (x >= 60) {

                        long minutes = x / 60;
                        long seconds = x % 60;
                        NumberFormat f = new DecimalFormat("00");
                        timeTxt.setText(f.format(minutes) + ":" + f.format(seconds));
                    } else {
                        timeTxt.setText("00:" + x);
                    }


                    if (progress > 0 && audioRecording.mMediaPlayer != null && !audioRecording.mMediaPlayer.isPlaying()) {
                        //  clearMediaPlayer();
                        if (!isPause) {
                            playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));
                            seekBar.setProgress(0);
                        }

                    }
                    if (audioRecording.mMediaPlayer != null && progress == audioRecording.mMediaPlayer.getDuration()) {
                        playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));
                        seekBar.setProgress(0);
                        isPause = false;
                    }
                }

                @Override
                public void onStopTrackingTouch(SeekBar seekBar) {


                    if (audioRecording.mMediaPlayer != null && audioRecording.mMediaPlayer.isPlaying()) {
                        audioRecording.mMediaPlayer.seekTo(seekBar.getProgress());
                    }
                }
            });
        });


        detailsArea = (RelativeLayout) findViewById(R.id.detailsArea);
        loading = (ProgressBar) findViewById(R.id.loading);
        headerArea.setVisibility(View.GONE);
        detailsArea.setVisibility(View.GONE);
        loading.setVisibility(View.VISIBLE);
        LBL_MINIMUM_ORDER_NOTE = generalFunc.retrieveLangLBl("", "LBL_MINIMUM_ORDER_NOTE");
        editCartImageview = (ImageView) findViewById(R.id.editCartImageview);
        addToClickHandler(editCartImageview);

        rightImgView = (ImageView) findViewById(R.id.rightImgView);
        if (getIntent().getBooleanExtra("isPrescription", false)) {
            rightImgView.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_medical_prescription));
            addToClickHandler(rightImgView);
        }


//        SYSTEM_PAYMENT_FLOW="Method-1";
//        APP_PAYMENT_MODE="Card";


        instructionslit = new ArrayList<>();
        moreinstructionLyout = findViewById(R.id.moreinstructionLyout);
        moreinstuction = findViewById(R.id.moreinstuction);
        moreInstructionAdapter = new MoreInstructionAdapter(getActContext(), instructionslit, new MoreInstructionAdapter.OnItemCheckListener() {
            @Override
            public void onItemCheck(HashMap<String, String> map) {
                currentSelectedItems.add(map);
                checkPrefreance();
                getPreferenceIds();

            }

            @Override
            public void onItemUncheck(HashMap<String, String> map) {
                currentSelectedItems.remove(map);
                checkPrefreance();
                getPreferenceIds();

            }
        });
        moreinstuction.setAdapter(moreInstructionAdapter);

        PreferenceHelp = findViewById(R.id.PreferenceHelp);
        PreferenceHelp.setOnClickListener(v -> showPreferenceHelp(null, "LBL_DELIVERY_PREFERENCE_NOTE", Utils.getText(textmoreinstruction)));



        /*Take away*/
        deliveryTypeArea = (LinearLayout) findViewById(R.id.deliveryTypeArea);
        addToClickHandler(deliveryTypeArea);

        tvDeliveryTypeTxt = (MTextView) findViewById(R.id.tvDeliveryTypeTxt);
        takeAwayRadioGroups = (RadioGroup) findViewById(R.id.takeAwayRadioGroups);
        takeAwayRadio = (RadioButton) findViewById(R.id.takeAwayRadio);
        deliverToDoorRadio = (RadioButton) findViewById(R.id.deliverToDoorRadio);
        tvDeliveryTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_TYPE"));
        takeAwayRadio.setText(generalFunc.retrieveLangLBl("", "LBL_TAKE_AWAY"));
        deliverToDoorRadio.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVER_TO_YOUR_DOORS"));
        help_deliverto_door = (ImageView) findViewById(R.id.help_deliverto_door);
        help_takeaway = (ImageView) findViewById(R.id.help_takeaway);
        help_deliverto_door.setOnClickListener(v -> showPreferenceHelp(getResources().getDrawable(R.drawable.ic_delivertodoor), "LBL_NOTE_DELIVER_TO_DOOR", deliverToDoorRadio.getText().toString()));
        help_takeaway.setOnClickListener(v -> showPreferenceHelp(getResources().getDrawable(R.drawable.ic_takeaway), "LBL_NOTE_TAKE_AWAY", takeAwayRadio.getText().toString()));


        eTakeAway = deliverToDoorRadio.getHint().toString();
        deliverToDoorRadio.setOnClickListener(v -> {
            if (takeAwayRadio.isChecked()) {
                takeAwayRadio.setChecked(false);
                eTakeAway = deliverToDoorRadio.getHint().toString();
                CheckOutOrderEstimateDetails(appliedPromoCode, false);

                if (generalFunc.getJsonValue("ENABLE_DELIVERY_INSTRUCTIONS_ORDERS", userProfileJson) != null &&
                        generalFunc.getJsonValue("ENABLE_DELIVERY_INSTRUCTIONS_ORDERS", userProfileJson).equalsIgnoreCase(
                                "Yes")) {
                    delveryVoiceInstArea.setVisibility(View.VISIBLE);
                } else {
                    delveryVoiceInstArea.setVisibility(View.GONE);
                }
                managePaymentOptions();
            }
        });

        takeAwayRadio.setOnClickListener(v -> {
            if (deliverToDoorRadio.isChecked()) {
                deliverToDoorRadio.setChecked(false);
                eTakeAway = takeAwayRadio.getHint().toString();
                //Remove selected Tip
                removeSelectedTip(true);

                delveryVoiceInstArea.setVisibility(View.GONE);
                managePaymentOptions();
            }
        });



        /*Take away*/

        textmoreinstruction = findViewById(R.id.textmoreinstruction);
        storeImg = findViewById(R.id.storeImg);

        locationArea = (LinearLayout) findViewById(R.id.locationArea);
        EditAddressArea = (LinearLayout) findViewById(R.id.EditAddressArea);
        AddAddressArea = (LinearLayout) findViewById(R.id.AddAddressArea);
        addAddressHTxt = (MTextView) findViewById(R.id.addAddressHTxt);
        addAddressBtn = (MTextView) findViewById(R.id.addAddressBtn);
        addToClickHandler(addAddressBtn);
        selLocTxt = (MTextView) findViewById(R.id.selLocTxt);
        resAddressTxtView = (MTextView) findViewById(R.id.resAddressTxtView);
        errorView = (ErrorView) findViewById(R.id.errorView);
        deliveryInstructionBox = (MaterialEditText) findViewById(R.id.deliveryInstructionBox);
        deliverinstruction = (MTextView) findViewById(R.id.deliverinstruction);
        deliverinstruction.setText(generalFunc.retrieveLangLBl("Instruction for Restaurant", "LBL_TITLE_STORE_INS"));
        checkboxWallet = (AppCompatCheckBox) findViewById(R.id.checkboxWallet);
        walletAmountTxt = (MTextView) findViewById(R.id.walletAmountTxt);
        contentArea = (ScrollView) findViewById(R.id.contentArea);
        nestedScroll = (NestedScrollView) findViewById(R.id.nestedScroll);

        View shadowHeaderView = findViewById(R.id.shadowHeaderView);
        shadowHeaderView.setVisibility(View.INVISIBLE);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            contentArea.setOnScrollChangeListener((v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
                if (!v.canScrollVertically(-1)) {
                    shadowHeaderView.setVisibility(View.INVISIBLE);
                } else {
                    shadowHeaderView.setVisibility(View.VISIBLE);
                }
            });
        }

        bottomArea = (LinearLayout) findViewById(R.id.bottomArea);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        promocodeArea = (LinearLayout) findViewById(R.id.promocodeArea);
        promocodeappliedHTxt = (MTextView) findViewById(R.id.promocodeappliedHTxt);
        promocodeappliedVTxt = (MTextView) findViewById(R.id.promocodeappliedVTxt);
        appliedPromoHTxtView = (MTextView) findViewById(R.id.appliedPromoHTxtView);
        appliedPromoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));

        backImgView = (ImageView) findViewById(R.id.backImgView);

        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        btn_clearcart = ((MaterialRippleLayout) findViewById(R.id.btn_clearcart)).getChildView();
        itemContainer = (LinearLayout) findViewById(R.id.itemContainer);
        restaurantNameTxtView = (MTextView) findViewById(R.id.restaurantNameTxtView);
        restaurantNameTxtView.setSelected(true);
        applyCouponHTxt = (MTextView) findViewById(R.id.applyCouponHTxt);
        applyCouponVTxt = (MTextView) findViewById(R.id.applyCouponVTxt);
        chargesHTxt = (MTextView) findViewById(R.id.chargesHTxt);
        couponCodeArea = findViewById(R.id.couponCodeArea);
        noLoginNoteTxt = (MTextView) findViewById(R.id.noLoginNoteTxt);

        paymentModeTitle = (MTextView) findViewById(R.id.paymentModeTitle);

        MyUtils.editBoxMultiLine(deliveryInstructionBox);

        farecontainer = (LinearLayout) findViewById(R.id.farecontainer);
        maxItemarea = (LinearLayout) findViewById(R.id.maxItemarea);
        btn_type2area = (LinearLayout) findViewById(R.id.btn_type2area);
        maxitemTitleTxtView = (MTextView) findViewById(R.id.maxitemTitleTxtView);
        maxitemmsgTxtView = (MTextView) findViewById(R.id.maxitemmsgTxtView);


        couponCodeImgView = (ImageView) findViewById(R.id.couponCodeImgView);
        couponCodeCloseImgView = (ImageView) findViewById(R.id.couponCodeCloseImgView);


        if (generalFunc.isRTLmode()) {
            couponCodeImgView.setRotation(180);
            backImgView.setRotation(180);
        }
/*
        takeAwayRadioGroups.setOnCheckedChangeListener(new RadioGroup.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(RadioGroup group, int checkedId) {
                currentSelectedItems.clear();


                if (checkedId == takeAwayRadio.getId()) {
                    eTakeAway = takeAwayRadio.getHint().toString();

                } else if (checkedId == deliverToDoorRadio.getId()) {
                    eTakeAway = deliverToDoorRadio.getHint().toString();
                }
                //comment By CS Because getting issue for getting preference List
                //if (isPreference) {
                CheckOutOrderEstimateDetails(appliedPromoCode, false);
                // }

            }
        });*/

        checkboxWallet.setOnClickListener(view -> {

            if (iswalletZero) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 0) {
                        checkboxWallet.setChecked(false);
                        generateAlert.closeAlertBox();
                    } else {


                        checkboxWallet.setChecked(false);
                        if (APP_PAYMENT_MODE.equalsIgnoreCase("Cash")) {
                            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                        } else {
                            Bundle bn = new Bundle();
                            bn.putString("iServiceId", generalFunc.getServiceId());
                            bn.putString("isCheckout", "");
                            new ActUtils(getActContext()).startActWithData(MyWalletActivity.class, bn);
                        }

                    }

                });
                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BAL"));

                if (APP_PAYMENT_MODE.equalsIgnoreCase("Cash")) {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                } else {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_ADD_NOW"));
                }
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                generateAlert.showAlertBox();


            } else {
                if (iswalletZero) {
                    return;
                }
                if (checkboxWallet.isChecked()) {
                    isWalletSelect = "Yes";
                } else {
                    isWalletSelect = "No";

                }
                CheckOutOrderEstimateDetails(appliedPromoCode, false);
            }

        });


        editLocation = (ImageView) findViewById(R.id.editLocation);
        addToClickHandler(editLocation);
        payArea = (LinearLayout) findViewById(R.id.payArea);
        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            payArea.setVisibility(View.GONE);
        }
        addToClickHandler(payArea);
        addressTxt = (MTextView) findViewById(R.id.addressTxt);
        delveryAddressArea = findViewById(R.id.delveryAddressArea);
        addToClickHandler(couponCodeArea);
        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(btn_clearcart);
        addToClickHandler(backImgView);

        Bundle bundle = getIntent().getExtras();
        if (bundle != null) {
            isFromEditCard = bundle.getBoolean("isFromEditCard");
        }

        setLabel();

        if (!generalFunc.retrieveValue(Utils.SELECT_ADDRESSS).equalsIgnoreCase("")) {
            selAddress = generalFunc.retrieveValue(Utils.SELECT_ADDRESSS);
            selLatitude = generalFunc.retrieveValue(Utils.SELECT_LATITUDE);
            selLongitude = generalFunc.retrieveValue(Utils.SELECT_LONGITUDE);
            selAddressId = generalFunc.retrieveValue(Utils.SELECT_ADDRESS_ID);
            addressTxt.setText(selAddress);
        }


        handleWalletView();


        checkboxWallet.setVisibility(View.GONE);
        walletAmountTxt.setVisibility(View.GONE);


    }

    private void managePaymentOptions() {
        if (isContectLessPrefSelected || eTakeAway.equals("Yes")) {
            btn_type2.setEnabled(false);
        }
        getProfilePaymentModel.getProfilePayment(Utils.eSystem_Type, this, this, isContectLessPrefSelected, eTakeAway.equals("Yes"));
    }

    private void manageProfilePayment() {
        payTypeTxt.setText(generalFunc.getJsonValue("PAYMENT_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString());
        btn_type2.setEnabled(true);
        organizationTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
        payImgView.setVisibility(View.VISIBLE);
        errorImage.setVisibility(View.GONE);
        showDropDownArea.setVisibility(View.VISIBLE);

        if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("")) {
            payTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT"));
            errorImage.setVisibility(View.VISIBLE);
            payImgView.setVisibility(View.GONE);
            showDropDownArea.setVisibility(View.GONE);
            btn_type2.setEnabled(false);
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                btn_type2.setEnabled(true);
            }

        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CASH")) {
            payImgView.setImageResource(R.drawable.ic_money_cash);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CARD")) {
            payImgView.setImageResource(R.mipmap.ic_card_new);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("BUSINESS")) {
            payImgView.setImageResource(R.drawable.ic_business_pay);
        } else {
            payImgView.setImageResource(R.drawable.ic_menu_wallet);
        }


    }

    private void manageInvoiceAsperPayment() {
        CheckOutOrderEstimateDetails(appliedPromoCode, false);
    }

    private void pauseMediaPlayer() {
        if (audioRecording != null && audioRecording.mMediaPlayer != null) {
            isPause = true;
            audioRecording.mMediaPlayer.pause();
            //   audioRecording.mMediaPlayer.release();
            playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));
        }
    }

    private void clearMediaPlayer() {
        if (audioRecording != null && audioRecording.mMediaPlayer != null) {
            audioRecording.mMediaPlayer.stop();
            audioRecording.mMediaPlayer.release();
            audioRecording.mMediaPlayer = null;

            seekbar.setProgress(0);
            wasPlaying = true;
            Log.d("wasPlaying", "::00::" + wasPlaying);
            playBtn.setImageDrawable(ContextCompat.getDrawable(CheckOutActivity.this, R.drawable.ic_baseline_play_arrow_24));

        }
    }

    private String getPreferenceIds() {
        int size = currentSelectedItems.size();
        String ids = "";
        if (isPreference && size > 0) {
            for (int i = 0; i < size; i++) {
                HashMap<String, String> map = currentSelectedItems.get(i);
                if (i == size - 1) {
                    ids = ids + map.get("iPreferenceId");
                } else {
                    ids = map.get("iPreferenceId") + "," + ids;
                }
            }
        }

        Logger.d("SelectedIds", "getPreferenceIds" + ids);
        return ids;
    }

    private void checkPrefreance() {

        isContectLessPrefSelected = false;
        for (int i1 = 0; i1 < currentSelectedItems.size(); i1++) {
            if (currentSelectedItems.get(i1).get("eContactLess").equalsIgnoreCase("Yes") && currentSelectedItems.get(i1).get("isSelect").equalsIgnoreCase("Yes")) {
                isContectLessPrefSelected = true;
            }
        }
        // getProfilePaymentModel.getProfilePayment(Utils.eSystem_Type, this, this, isContectLessPrefSelected);
        managePaymentOptions();

    }

    private boolean checkSelPrefrence(String iPreferenceId) {
        boolean isSelect = false;
        for (int i1 = 0; i1 < currentSelectedItems.size(); i1++) {
            if (currentSelectedItems.get(i1).get("iPreferenceId").equalsIgnoreCase(iPreferenceId)) {
                isSelect = true;
            }
        }

        return isSelect;

    }

    @Override
    protected void onPause() {
        super.onPause();
        pauseMediaPlayer();
    }

    private void handleWalletView() {

        String WALLET_ENABLE = userProfileJson != null ? generalFunc.getJsonValue(Utils.WALLET_ENABLE, userProfileJson) : "";

        if (!WALLET_ENABLE.equals("") && WALLET_ENABLE.equalsIgnoreCase("Yes") && !generalFunc.getJsonValue("user_available_balance_amount", userProfileJson).equalsIgnoreCase("") &&
                GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("user_available_balance_amount", userProfileJson)) > 0) {
            checkboxWallet.setVisibility(View.VISIBLE);
            walletAmountTxt.setVisibility(View.VISIBLE);
            iswalletZero = false;


        } else {
            iswalletZero = true;
            checkboxWallet.setVisibility(View.VISIBLE);
            walletAmountTxt.setVisibility(View.VISIBLE);
        }

    }

    private void generateErrorView() {
//        contentArea.setVisibility(View.GONE);
//        bottomArea.setVisibility(View.GONE);

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        manageView();
        errorView.setOnRetryListener(() -> CheckOutOrderEstimateDetails(appliedPromoCode, false));
    }

    private void setLabel() {


        selLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_ADDRESS"));
//        addAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DELIVERY_LOCATION"));
        addAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_ADDRESS"));
        addAddressBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_ADDRESS_TXT"));


        paymentModeTitle.setText(generalFunc.retrieveLangLBl("", "LBL_SELECET_PAYMENT_MODE"));
//        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CART_SUMMARY"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHECKOUT"));


        checkboxWallet.setText(generalFunc.retrieveLangLBl("Use Wallet Balance", "LBL_USE_WALLET_BAL"));

        applyCouponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_COUPON"));
        applyCouponVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_PROMO_TXT"));
        chargesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));

        deliveryInstructionBox.setHint(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_INSTRUCTION"));
        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_LOGIN_TO_CONTINUE"));
        } else {
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_TXT"));


        }
        noLoginNoteTxt.setText(generalFunc.retrieveLangLBl("You will be able to review delivery address and payment options after login", "LBL_NO_LOGIN_NOTE"));

        if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
            noLoginNoteTxt.setVisibility(View.GONE);
            if (GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("ToTalAddress", userProfileJson)) > 0) {
                delveryAddressArea.setVisibility(View.VISIBLE);
                EditAddressArea.setVisibility(View.VISIBLE);
                AddAddressArea.setVisibility(View.GONE);
            } else {
                EditAddressArea.setVisibility(View.GONE);
                AddAddressArea.setVisibility(View.VISIBLE);

            }
        } else {
            noLoginNoteTxt.setVisibility(View.VISIBLE);
            delveryAddressArea.setVisibility(View.GONE);

        }

        maxitemTitleTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_TO_MANY_ITEMS"));
        maxitemmsgTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_MAX_QTY_NOTE") + " " + generalFunc.retrieveValue(Utils.COMPANY_MAX_QTY) + " " + generalFunc.retrieveLangLBl("", "LBL_TO_PROCEED"));
        btn_clearcart.setText(generalFunc.retrieveLangLBl("", "LBL_CLEAR_CART"));

    }

    @Override
    protected void onResume() {
        super.onResume();

        try {
            getUserProfileJson();

            handleWalletView();
            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                noLoginNoteTxt.setVisibility(View.GONE);
                locationArea.setVisibility(View.VISIBLE);
                if (GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0) {
                    delveryAddressArea.setVisibility(View.VISIBLE);
                }

                if (GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("ToTalAddress", userProfileJson)) > 0) {
                    delveryAddressArea.setVisibility(View.VISIBLE);
                    EditAddressArea.setVisibility(!eTakeAway.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
                    AddAddressArea.setVisibility(View.GONE);
                } else {
                    EditAddressArea.setVisibility(View.GONE);
                    AddAddressArea.setVisibility(!eTakeAway.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

                }

            } else {
                locationArea.setVisibility(View.GONE);
                noLoginNoteTxt.setVisibility(View.VISIBLE);
                delveryAddressArea.setVisibility(View.GONE);
            }
        } catch (Exception e) {

        }
    }

    private void getUserProfileJson() {
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        //SYSTEM_PAYMENT_FLOW = generalFunc.getJsonValue("SYSTEM_PAYMENT_FLOW", userProfileJson);
        //APP_PAYMENT_METHOD = generalFunc.getJsonValue("APP_PAYMENT_METHOD", userProfileJson);
        APP_PAYMENT_MODE = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson);
        // Tip Feature - set Values
        String CurrencySymbol = generalFunc.getJsonValue("CurrencySymbol", userProfileJson);
        String DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL = generalFunc.getJsonValue("DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL", userProfileJson);
        String tipAMount1 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("TIP_AMOUNT_1", userProfileJson));
        String tipAMount2 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("TIP_AMOUNT_2", userProfileJson));
        String tipAMount3 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("TIP_AMOUNT_3", userProfileJson));
        isPercentage = DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL.equalsIgnoreCase("Percentage");
        boolean eReverseSymbolEnable = generalFunc.retrieveValue("eReverseSymbolEnable").equalsIgnoreCase("Yes");
        tipAmountText1.setTag(isPercentage ? 1 : tipAMount1);
        tipAmountText1.setText(isPercentage ? tipAMount1 : eReverseSymbolEnable ? tipAMount1 + " " + CurrencySymbol : CurrencySymbol + tipAMount1);
        tipAmountText2.setTag(isPercentage ? 2 : tipAMount2);
        tipAmountText2.setText(isPercentage ? tipAMount2 : eReverseSymbolEnable ? tipAMount2 + " " + CurrencySymbol : CurrencySymbol + tipAMount2);
        tipAmountText3.setTag(isPercentage ? 3 : tipAMount3);
        tipAmountText3.setText(isPercentage ? tipAMount3 : eReverseSymbolEnable ? tipAMount3 + " " + CurrencySymbol : CurrencySymbol + tipAMount3);
        tipAmountOtherText.setTag(isPercentage ? 4 : "");
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);


        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {


            // Logger.d("onActivityResult", "::" + data.toString());
            if (Utils.checkText(data.getStringExtra("eTakeAway"))) {
                eTakeAway = data.getStringExtra("eTakeAway");
            }

            if (data.getStringExtra("isGenieOrderPlace") != null && data.getStringExtra("isGenieOrderPlace").equals("Yes")) {
                Bundle bn = new Bundle();
                if (data.getStringExtra("iOrderId") != null) {
                    bn.putString("iOrderId", data.getStringExtra("iOrderId"));
                }

                new ActUtils(getActContext()).startActWithData(OrderPlaceConfirmActivity.class, bn);

                return;

            }

            // getProfilePaymentModel.getProfilePayment(Utils.eSystem_Type, this, this, false);
            managePaymentOptions();
            manageInvoiceAsperPayment();


            // placeOrder();

        } else if (requestCode == RC_SIGN_IN_UP && resultCode == RESULT_OK) {


            noLoginNoteTxt.setVisibility(View.GONE);
            getUserProfileJson();
            setGeneralData();
            setButtonTxt();
//            if (GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0) {
//                Bundle bn = new Bundle();
//                bn.putString("iCompanyId", cartList.get(0).getiCompanyId());
//                new ActUtils(getActContext()).startActForResult(ListAddressActivity.class, bn, SEL_ADDRESS);
//            } else {
//                openAddressDailog();
//            }
            if (internetConnection.isNetworkConnected()) {
                CheckOutOrderEstimateDetails("", false);
            } else {
                generateErrorView();
            }
        } else if (requestCode == SEL_ADDRESS && resultCode == RESULT_OK) {

            getUserProfileJson();
            if (data.getStringExtra("ToTalAddress") != null) {
                ToTalAddress = data.getStringExtra("ToTalAddress");
            }
            if (GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0) {
                if (data != null && data.getStringExtra("address") != null) {
                    if (!selAddressId.equalsIgnoreCase(data.getStringExtra("addressId"))) {
                        appliedPromoCode = "";
                    }
                    addressTxt.setText(data.getStringExtra("address"));
                    selAddress = data.getStringExtra("address");
                    selAddressId = data.getStringExtra("addressId");

                    isselectaddress = true;

                    HashMap<String, String> storeData = new HashMap<>();
                    storeData.put(Utils.SELECT_ADDRESS_ID, selAddressId);
                    storeData.put(Utils.SELECT_ADDRESSS, data.getStringExtra("address"));
                    storeData.put(Utils.SELECT_LATITUDE, data.getStringExtra("vLatitude"));
                    storeData.put(Utils.SELECT_LONGITUDE, data.getStringExtra("vLongitude"));
                    generalFunc.storeData(storeData);

                    CheckOutOrderEstimateDetails(appliedPromoCode, false);
                }
                delveryAddressArea.setVisibility(View.VISIBLE);
            } else {
                delveryAddressArea.setVisibility(View.GONE);
                //openAddressDailog("");
                appliedPromoCode = "";
                CheckOutOrderEstimateDetails(appliedPromoCode, false);

            }

        } else if (requestCode == SEL_ADDRESS) {

            if (data != null && data.getBooleanExtra("isDeleted", false)) {

                ArrayList<String> removeData = new ArrayList<>();
                removeData.add(Utils.SELECT_ADDRESS_ID);
                removeData.add(Utils.SELECT_ADDRESSS);
                removeData.add(Utils.SELECT_LATITUDE);
                removeData.add(Utils.SELECT_LONGITUDE);
                generalFunc.removeValue(removeData);
                appliedPromoCode = "";
                CheckOutOrderEstimateDetails(appliedPromoCode, false);
            }

        } else if (requestCode == ADD_ADDRESS) {

            if (resultCode == RESULT_OK) {
                getUserProfileJson();
                if (internetConnection.isNetworkConnected()) {
                    generalFunc.removeValue(Utils.SELECT_ADDRESS_ID);
                    appliedPromoCode = "";
                    CheckOutOrderEstimateDetails(appliedPromoCode, false);
                } else {
                    generateErrorView();
                }
            } else {
                onBackPressed();
            }
        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE) {
            getUserProfileJson();
        } else if (requestCode == Utils.SOCIAL_LOGIN_REQ_CODE && resultCode == RESULT_OK) {
            getUserProfileJson();

            String ePhoneVerified = generalFunc.getJsonValue("ePhoneVerified", userProfileJson);

            if (!ePhoneVerified.equals("Yes")) {
                notifyVerifyMobile();
                return;
            }


        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == RESULT_OK && data != null) {

            getUserProfileJson();


            // addDrawer.changeUserProfileJson(this.userProfileJson);
        } else if (data != null && requestCode == Utils.SELECT_COUPON_REQ_CODE && resultCode == RESULT_OK) {
            String couponCode = data.getStringExtra("CouponCode");
            if (couponCode == null) {
                couponCode = "";
            }
            appliedPromoCode = couponCode;
            CheckOutOrderEstimateDetails(couponCode, true);
            setPromoCode();
        } else if (requestCode == Utils.REQ_VERIFY_INSTANT_PAYMENT_CODE && resultCode == RESULT_OK && data != null) {

//            if (data != null) {
            orderCompleted();
//            } else {
//                generalFunc.showError();
//            }


        } else if (requestCode == Utils.SELECT_ORGANIZATION_PAYMENT_CODE) {

            if (data != null && resultCode == RESULT_OK) {
                if (data.getSerializableExtra("data").equals("")) {


                    if (data.getBooleanExtra("isCash", false)) {
                        ePaymentOption = "Cash";
                    } else {
                        ePaymentOption = "Card";

                       /* if (ServiceModule.DeliverAllProduct) {
                            selectedMethod = "Instant";
                        } else {*/
                        //}
                    }


                    if (data.getBooleanExtra("isWallet", false)) {
                        isWalletSelect = "Yes";

                    } else {
                        isWalletSelect = "No";

                    }
                    if (Utils.checkText(data.getStringExtra("eTakeAway"))) {
                        eTakeAway = data.getStringExtra("eTakeAway");
                    }

                    placeOrder();


                }

            }
        }

    }

    private void orderCompleted() {
        Bundle bn = new Bundle();
        bn.putBoolean("isRestart", true);
        bn.putString("iServiceId", generalFunc.getServiceId());
        bn.putString("iOrderId", iOrderId);
        bn.putString("eTakeAway", eTakeAway);
        new ActUtils(getActContext()).startActWithData(OrderPlaceConfirmActivity.class, bn);
    }

    private void notifyVerifyMobile() {
        String vPhoneCode = generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        Bundle bn = new Bundle();
        bn.putString("MOBILE", vPhoneCode + generalFunc.getJsonValue("vPhone", userProfileJson));
        bn.putString("msg", "DO_PHONE_VERIFY");
        bn.putBoolean("isrestart", false);
        new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
    }

    private void defaultPromoView() {
        appliedPromoCode = "";
        promocodeArea.setVisibility(View.GONE);
        appliedPromoHTxtView.setVisibility(View.GONE);


        couponCodeImgView.setVisibility(View.VISIBLE);
        couponCodeCloseImgView.setVisibility(View.GONE);
        applyCouponVTxt.setTextColor(Color.parseColor("#333333"));
        applyCouponVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_PROMO_TXT"));

        promocodeappliedHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
    }

    private void appliedPromoView() {
        appliedPromoHTxtView.setVisibility(View.VISIBLE);
        applyCouponVTxt.setText(appliedPromoCode);
        applyCouponVTxt.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
        couponCodeImgView.setVisibility(View.GONE);
        couponCodeCloseImgView.setVisibility(View.VISIBLE);
        addToClickHandler(couponCodeCloseImgView);
        appliedPromoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
    }

    private void manageWalletCheckBox() {
        checkboxWallet.setVisibility(View.VISIBLE);
        walletAmountTxt.setVisibility(View.VISIBLE);
        iswalletZero = true;
    }

    private void CheckOutOrderEstimateDetails(String promoCode, boolean isPromoApplied) {
        Logger.d("CheckPromoValue", "::" + promoCode);

        try {
            getLocalData();
            JSONArray orderedItemArr = new JSONArray();
            for (int i = 0; i < cartList.size(); i++) {
                JSONObject object = new JSONObject();
                object.put("iMenuItemId", cartList.get(i).getiMenuItemId());
                object.put("iFoodMenuId", cartList.get(i).getiFoodMenuId());
                object.put("vOptionId", cartList.get(i).getiOptionId());
                object.put("vAddonId", cartList.get(i).getiToppingId());
                object.put("iQty", cartList.get(i).getQty());
                orderedItemArr.put(object);
            }


            HashMap<String, String> parameters = new HashMap<String, String>();
            parameters.put("type", "CheckOutOrderEstimateDetails");
            parameters.put("iUserId", generalFunc.getMemberId());
            parameters.put("iCompanyId", cartList.get(0).getiCompanyId());
            if (!eTakeAway.equalsIgnoreCase("Yes")) {
                HashMap<String, String> data = new HashMap<>();
                data.put(Utils.SELECT_ADDRESS_ID, "");
                data.put(Utils.SELECT_LATITUDE, "");
                data.put(Utils.SELECT_LONGITUDE, "");
                data = generalFunc.retrieveValue(data);
                parameters.put("iUserAddressId", data.get(Utils.SELECT_ADDRESS_ID));
                parameters.put("PassengerLat", data.get(Utils.SELECT_LATITUDE));
                parameters.put("PassengerLon", data.get(Utils.SELECT_LONGITUDE));
            }
            parameters.put("vCouponCode", promoCode.trim());
            parameters.put("ePaymentOption", ePaymentOption);
            parameters.put("vInstruction", deliveryInstructionBox.getText().toString().trim());
            parameters.put("OrderDetails", orderedItemArr.toString());
            parameters.put("CheckUserWallet", isWalletSelect);
            parameters.put("eSystem", Utils.eSystem_Type);

            if (Utils.checkText(eTakeAway)) {
                parameters.put("eTakeAway", eTakeAway);
            }
            /*Tip Feature - Parameters */
            if (Utils.checkText(tipAmount)) {
                if (isPercentage) {
                    parameters.put("selectedTipPos", tipAmount);
                    if (tipAmount.equalsIgnoreCase("4")) {
                        parameters.put("fTipAmount", Utils.getText(tipAmountBox));
                    }
                } else {
                    parameters.put("fTipAmount", tipAmount);

                }
            }

            if (isAdjustPress) {
                parameters.put("isAddOutstandingAmt", "Yes");
            }


            ApiHandler.execute(getActContext(), parameters, headerArea.getVisibility() == View.VISIBLE ? true : false, false, generalFunc, responseString -> {
                JSONObject responseObj = generalFunc.getJsonObject(responseString);

                if (responseObj != null) {

                    manageView();

                    String action = generalFunc.getJsonValueStr(Utils.action_str, responseObj);

                    if (action.equals("1")) {
                        editCartImageview.setVisibility(View.VISIBLE);
                        if (getIntent().getBooleanExtra("isPrescription", false)) {
                            rightImgView.setVisibility(View.VISIBLE);
                        }

                        eProofUpload = generalFunc.getJsonValueStr("eProofUpload", responseObj);
                        eShowTerms = generalFunc.getJsonValueStr("eShowTerms", responseObj);
                        tProofNote = generalFunc.getJsonValueStr("tProofNote", responseObj);
                        headerArea.setVisibility(View.VISIBLE);
                        detailsArea.setVisibility(View.VISIBLE);
                        loading.setVisibility(View.GONE);
                        errorView.setVisibility(View.GONE);
                        appliedPromoCode = promoCode;

                        int sWidth = getResources().getDimensionPixelSize(R.dimen._68sdp);
                        int sHeight = (int) (sWidth / 1.33333333333);

                        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) storeImg.getLayoutParams();
                        mParams.width = sWidth;
                        mParams.height = sHeight;
                        storeImg.setLayoutParams(mParams);

                        String Url = generalFunc.getJsonValueStr("vImage", responseObj);
                        if (!Utils.checkText(Url)) {
                            Url = "Temp";
                        }
                        Url = Utils.getResizeImgURL(getActContext(), Url, sWidth, sHeight);
                        new LoadImage.builder(LoadImage.bind(Url), storeImg).setPlaceholderImagePath(R.color.imageBg).setErrorImagePath(R.color.imageBg).build();

                        lastOrderPaymentMethod = generalFunc.getJsonValueStr("lastOrderPaymentMethod", responseObj);
                        if (lastOrderPaymentMethod != null && !lastOrderPaymentMethod.equalsIgnoreCase("")) {
                            payArea.setVisibility(View.VISIBLE);
                        }
                        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                            payArea.setVisibility(View.GONE);
                        }

                        resAddressTxtView.setText(generalFunc.getJsonValueStr("vCaddress", responseObj));


                        // if (SYSTEM_PAYMENT_FLOW.equalsIgnoreCase("Method-1")) {

                        if (generalFunc.getJsonValueStr("DISABLE_CASH_PAYMENT_OPTION", responseObj).equalsIgnoreCase("yes")) {

                            if (Utils.checkText(APP_PAYMENT_MODE)/* && !APP_PAYMENT_MODE.equalsIgnoreCase("Cash")*/) {

                                isCODAllow = false;
                                outStandingAmount = generalFunc.getJsonValueStr("fOutStandingAmount", responseObj);


                            }

                            manageWalletCheckBox();

                        } else {
                            String WALLET_ENABLE = userProfileJson != null ? generalFunc.getJsonValue(Utils.WALLET_ENABLE, userProfileJson) : "";
                            manageWalletCheckBox();


                        }
                        // }


                        JSONObject DeliveryPreferences = generalFunc.getJsonObject("DeliveryPreferences", responseObj);

                        isPreference = generalFunc.getJsonValueStr("Enable", DeliveryPreferences).equalsIgnoreCase("Yes") ? true : false;
                        if (isPreference) {
                            moreinstructionLyout.setVisibility(View.VISIBLE);
                            instructionslit.clear();
                            textmoreinstruction.setText(generalFunc.getJsonValueStr("vTitle", DeliveryPreferences));
                            JSONArray Data = generalFunc.getJsonArray("Data", DeliveryPreferences);
                            for (int i = 0; i < Data.length(); i++) {
                                try {
                                    JSONObject jsonObject = (JSONObject) Data.get(i);
                                    String tTitle = generalFunc.getJsonValueStr("tTitle", jsonObject);
                                    String tDescription = generalFunc.getJsonValueStr("tDescription", jsonObject);
                                    String ePreferenceFor = generalFunc.getJsonValueStr("ePreferenceFor", jsonObject);
                                    String eImageUpload = generalFunc.getJsonValueStr("eImageUpload", jsonObject);
                                    String iDisplayOrder = generalFunc.getJsonValueStr("iDisplayOrder", jsonObject);
                                    String eContactLess = generalFunc.getJsonValueStr("eContactLess", jsonObject);
                                    String eStatus = generalFunc.getJsonValueStr("eStatus", jsonObject);
                                    String iPreferenceId = generalFunc.getJsonValueStr("iPreferenceId", jsonObject);
                                    HashMap<String, String> hashMap = new HashMap<>();

                                    hashMap.put("tTitle", tTitle);
                                    hashMap.put("tDescription", tDescription);
                                    hashMap.put("ePreferenceFor", ePreferenceFor);
                                    hashMap.put("eImageUpload", eImageUpload);
                                    hashMap.put("iDisplayOrder", iDisplayOrder);
                                    hashMap.put("eContactLess", eContactLess);
                                    hashMap.put("eStatus", eStatus);
                                    hashMap.put("iPreferenceId", iPreferenceId);
                                    if (currentSelectedItems != null) {
                                        if (checkSelPrefrence(iPreferenceId)) {
                                            hashMap.put("isSelect", "Yes");
                                        } else {
                                            hashMap.put("isSelect", "No");
                                        }
                                    } else {
                                        hashMap.put("isSelect", "No");
                                    }
                                    instructionslit.add(hashMap);

                                } catch (JSONException e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                }

                            }

                            moreInstructionAdapter.notifyDataSetChanged();

                        } else {
                            moreinstructionLyout.setVisibility(View.GONE);
                        }

                        currencySymbol = generalFunc.getJsonValueStr("currencySymbol", responseObj);
                        itemArraylist.clear();
                        JSONArray OrderDetailsItemsArr = generalFunc.getJsonArray("OrderDetailsItemsArr", responseObj);

                        if (itemContainer.getChildCount() > 0) {
                            itemContainer.removeAllViewsInLayout();
                        }

                        if (OrderDetailsItemsArr != null && OrderDetailsItemsArr.length() > 0) {
                            for (int j = 0; j < OrderDetailsItemsArr.length(); j++) {
                                JSONObject itemObj = generalFunc.getJsonObject(OrderDetailsItemsArr, j);
                                HashMap<String, String> itemmap = new HashMap<>();
                                itemmap.put("iMenuItemId", generalFunc.getJsonValueStr("iMenuItemId", itemObj));
                                itemmap.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", itemObj));
                                itemmap.put("vItemType", generalFunc.getJsonValueStr("vItemType", itemObj));
                                itemmap.put("iQty", generalFunc.getJsonValueStr("iQty", itemObj));
                                itemmap.put("fOfferAmt", generalFunc.getJsonValueStr("fOfferAmt", itemObj));
                                itemmap.put("fOriginalPrice", generalFunc.getJsonValueStr("fOriginalPrice", itemObj));
                                itemmap.put("fPrice", generalFunc.getJsonValueStr("fPrice", itemObj));
                                itemmap.put("currencySymbol", generalFunc.getJsonValueStr("currencySymbol", itemObj));
                                itemmap.put("optionaddonname", generalFunc.getJsonValueStr("optionaddonname", itemObj));
                                itemmap.put("vImage", generalFunc.getJsonValueStr("vImage", itemObj));
                                itemArraylist.add(itemmap);

                            }
                            addItemView();
                        }

                        if (eTakeAway.equalsIgnoreCase("Yes")) {
                            prevselectaddress = isselectaddress;
                            isselectaddress = true;
                            EditAddressArea.setVisibility(View.GONE);
                            AddAddressArea.setVisibility(View.GONE);
                        } else {
                            isselectaddress = prevselectaddress;
                            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                                EditAddressArea.setVisibility(View.GONE);
                                AddAddressArea.setVisibility(View.GONE);
                            } else {
                                ToTalAddress = generalFunc.getJsonValueStr("ToTalAddress", responseObj);
                                EditAddressArea.setVisibility(GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0 ? View.VISIBLE : View.GONE);
                                AddAddressArea.setVisibility(GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0 ? View.GONE : View.VISIBLE);
                            }
                        }
                        //Tip Feature - Tip Area Show Setting
                        String ENABLE_TIP_MODULE_DELIVERALL_ = generalFunc.getJsonValue("ENABLE_TIP_MODULE_DELIVERALL", userProfileJson);
                        tipCardArea.setVisibility(eTakeAway.equalsIgnoreCase("No") && !generalFunc.getMemberId().equals("") && ENABLE_TIP_MODULE_DELIVERALL_.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

                        if (!isselectaddress) {
                            String UserSelectedAddressId = generalFunc.getJsonValueStr("UserSelectedAddressId", responseObj);

                            if (UserSelectedAddressId != null && !UserSelectedAddressId.equalsIgnoreCase("")) {
                                HashMap<String, String> storeData = new HashMap<>();
                                storeData.put(Utils.SELECT_ADDRESSS, generalFunc.getJsonValueStr("UserSelectedAddress", responseObj));
                                storeData.put(Utils.SELECT_LATITUDE, generalFunc.getJsonValueStr("UserSelectedLatitude", responseObj));
                                storeData.put(Utils.SELECT_LONGITUDE, generalFunc.getJsonValueStr("UserSelectedLongitude", responseObj));
                                storeData.put(Utils.SELECT_ADDRESS_ID, UserSelectedAddressId);
                                generalFunc.storeData(storeData);

                                addressTxt.setText(generalFunc.retrieveValue(Utils.SELECT_ADDRESSS));

                            } else {
                                isselectaddress = false;
                                addressTxt.setText(generalFunc.retrieveValue(Utils.SELECT_ADDRESSS));
                            }
                        } else {
                            isselectaddress = false;
                            addressTxt.setText(generalFunc.retrieveValue(Utils.SELECT_ADDRESSS));

                        }

                        restaurantstatus = generalFunc.getJsonValueStr("restaurantstatus", responseObj);
                        //LBL_DELIVERY_CHARGE
                        if (!generalFunc.getMemberId().equals("") && !generalFunc.getJsonValueStr("fNetTotalAmount", responseObj).equalsIgnoreCase("0")) {
                            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_TXT"));
                            if (eProofUpload.equalsIgnoreCase("Yes")) {
                                btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                            }
                        } else {
                            if (!generalFunc.getMemberId().equals("")) {
                                btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_TXT"));
                                if (eProofUpload.equalsIgnoreCase("Yes")) {
                                    btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                                }
                            }
                        }


                        String DisplayUserWalletDebitAmount = generalFunc.getJsonValueStr("DisplayUserWalletDebitAmount", responseObj);
                        if (DisplayUserWalletDebitAmount != null &&
                                !DisplayUserWalletDebitAmount.equalsIgnoreCase("")) {
                            walletAmountTxt.setText(generalFunc.convertNumberWithRTL(DisplayUserWalletDebitAmount));
                        } else {
                            walletAmountTxt.setText("");
                        }

                        Double fDiscount = generalFunc.parseDoubleValue(0, generalFunc.getJsonValueStr("fDiscount", responseObj));

                        if (generalFunc.getJsonValueStr("eFreeDelivery", responseObj).equalsIgnoreCase("")
                                || generalFunc.getJsonValueStr("eFreeDelivery", responseObj).equalsIgnoreCase("No")) {
                            if (fDiscount > 0) {
                                appliedPromoView();
                            } else {
                                defaultPromoView();
                            }
                        }


//                            if (isPromoApplied) {
//                                // generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PROMO_APPLIED"));
//                            } else {
//                                defaultPromoView();
//                            }


                        finalTotal = Double.parseDouble(generalFunc.getJsonValueStr("fSubTotalamount", responseObj));

                        JSONArray FareDetailsArr = null;
                        FareDetailsArr = generalFunc.getJsonArray("FareDetailsArrNew", responseObj);

                        if (farecontainer.getChildCount() > 0) {
                            farecontainer.removeAllViews();
                        }
                        addFareDetailLayout(FareDetailsArr);


                        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                            isLogin = false;
                        } else {
                            ToTalAddress = generalFunc.getJsonValueStr("ToTalAddress", responseObj);
                            isLogin = true;
                            if (GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0) {
                                ToTalAddress = generalFunc.getJsonValueStr("ToTalAddress", responseObj);
                                try {
                                    onResume();
                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                }
                            } else {
                                // openAddressDailog(generalFunc.getJsonValueStr("RestaurantAddressNotMatch", responseObj));
                                AddAddressArea.setVisibility(View.VISIBLE);
                                EditAddressArea.setVisibility(View.GONE);
                            }
                        }
                    } else if (action.equals("01")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PROMOCODE_ALREADY_USED"));
                    } else {


                        if (generalFunc.getJsonValueStr(Utils.message_str, responseObj) != null && generalFunc.getJsonValueStr(Utils.message_str, responseObj).equalsIgnoreCase("LBL_INVALID_PROMOCODE")) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_INVALID_PROMOCODE"));
                            return;
                        }

                        if (generalFunc.getJsonValueStr(Utils.message_str, responseObj) != null && generalFunc.getJsonValueStr(Utils.message_str, responseObj).equalsIgnoreCase("LBL_PROMOCODE_COMPLETE_USAGE_LIMIT")) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PROMOCODE_COMPLETE_USAGE_LIMIT"));
                            return;
                        }

                        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                            isLogin = false;
                        } else {

                            if (generalFunc.getJsonValueStr("ToTalAddress", responseObj) != null) {
                                ToTalAddress = generalFunc.getJsonValueStr("ToTalAddress", responseObj);
                                isLogin = true;
                                if (GeneralFunctions.parseIntegerValue(0, ToTalAddress) > 0) {
                                    ToTalAddress = generalFunc.getJsonValueStr("ToTalAddress", responseObj);
                                    onResume();
                                } else {
                                    //openAddressDailog(generalFunc.getJsonValueStr("RestaurantAddressNotMatch", responseObj));
                                    AddAddressArea.setVisibility(View.VISIBLE);
                                    EditAddressArea.setVisibility(View.GONE);
                                    return;
                                }
                            }
                        }

                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));


                    }

                    if (generalFunc.getJsonValue("eTakeaway", responseObj) != null) {
                        deliveryTypeArea.setVisibility(generalFunc.getJsonValue("eTakeaway", responseObj).equals("Yes") ? View.VISIBLE : View.GONE);
                    }
                    if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                        deliveryTypeArea.setVisibility(View.GONE);
                    }
                } else {
                    generalFunc.showError();
                }
            });
        } catch (Exception e) {
            Logger.d("CheckEx", "::" + e.toString());
        }

    }

    private void addFareDetailLayout(JSONArray jobjArray) {


        boolean isLast;
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                isLast = i == jobjArray.length() - 1;
                String rName = jobject.names().getString(0);
                String rValue = jobject.get(rName).toString();
                farecontainer.addView(MyUtils.addFareDetailRow(getActContext(), generalFunc, rName, rValue, isLast));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }

    private void manageView() {
        if (internetConnection.isNetworkConnected()) {
            errorView.setVisibility(View.GONE);
        } else {
            errorView.setVisibility(View.VISIBLE);
        }
    }

    public void manageRecordView(boolean isStart) {
        runOnUiThread(() -> {

            if (isStart) {
                if (miscNoteArea != null) {
                    miscImg.setVisibility(View.GONE);
                    miscNoteArea.setVisibility(View.GONE);


                }
                if (headerArea != null) {
                    headerArea.requestDisallowInterceptTouchEvent(true);
                    moreinstructionLyout.requestDisallowInterceptTouchEvent(true);
                    nestedScroll.setNestedScrollingEnabled(false);


                }

            } else {
                if (miscNoteArea != null) {
                    miscImg.setVisibility(View.VISIBLE);
                    miscNoteArea.setVisibility(View.VISIBLE);


                    if (headerArea != null) {
                        headerArea.requestDisallowInterceptTouchEvent(false);
                        moreinstructionLyout.requestDisallowInterceptTouchEvent(false);
                        nestedScroll.setNestedScrollingEnabled(true);


                    }


                }
            }
        });

    }

    private void orderPlaceCall() {
        try {

            clearMediaPlayer();
            JSONArray orderedItemArr = new JSONArray();
            for (int i = 0; i < cartList.size(); i++) {
                JSONObject object = new JSONObject();
                object.put("iMenuItemId", cartList.get(i).getiMenuItemId());
                object.put("iFoodMenuId", cartList.get(i).getiFoodMenuId());
                object.put("vOptionId", cartList.get(i).getiOptionId());
                object.put("vAddonId", cartList.get(i).getiToppingId());
                object.put("iQty", cartList.get(i).getQty());
                orderedItemArr.put(object);
            }
            HashMap<String, String> parameters = new HashMap<String, String>();

            parameters.put("type", "CheckOutOrderDetails");
            parameters.put("iVoiceDirectionFileId", iVoiceDirectionFileId);
            parameters.put("iIdProofImageId", iIdProofImageId);
            parameters.put("iServiceId", generalFunc.getServiceId());
            parameters.put("iUserId", generalFunc.getMemberId());
            parameters.put("iCompanyId", cartList.get(0).getiCompanyId());
            parameters.put("iUserAddressId", generalFunc.retrieveValue(Utils.SELECT_ADDRESS_ID));
            parameters.put("vCouponCode", appliedPromoCode);
            parameters.put("ePaymentOption", ePaymentOption);
            parameters.put("vInstruction", deliveryInstructionBox.getText().toString().trim());
            parameters.put("OrderDetails", orderedItemArr.toString());
            parameters.put("CheckUserWallet", isWalletSelect);
            parameters.put("iOrderId", iOrderId);
            /*Tip Feature - Parameters */
            if (Utils.checkText(tipAmount)) {
                if (isPercentage) {
                    parameters.put("selectedTipPos", tipAmount);
                    if (tipAmount.equalsIgnoreCase("4")) {
                        parameters.put("fTipAmount", Utils.getText(tipAmountBox));
                    }
                } else {
                    parameters.put("fTipAmount", tipAmount);

                }
            }

            int size = currentSelectedItems.size();
            if (isPreference && size > 0) {
                parameters.put("selectedprefrences", getPreferenceIds());
            }


            if (!ePaymentOption.equalsIgnoreCase("Cash")) {
                parameters.put("CheckUserWallet", "Yes");
                parameters.put("ePayWallet", "Yes");

            }
            parameters.put("eSystem", Utils.eSystem_Type);
            parameters.put("eWalletIgnore", eWalletIgnore);

            if (Utils.checkText(eTakeAway)) {
                parameters.put("eTakeAway", eTakeAway);
            }

            if (isAdjustPress) {
                parameters.put("isAddOutstandingAmt", "Yes");
            }

            ApiHandler.execute(getActContext(), parameters, true, true, generalFunc, responseString -> {
                if (generalFunc.getJsonValue(Utils.message_str, responseString) != null && generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_PROMOCODE_COMPLETE_USAGE_LIMIT")) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PROMOCODE_COMPLETE_USAGE_LIMIT"));
                    return;
                }

                if (generalFunc.getJsonValue("isShowOutstanding", responseString).equalsIgnoreCase("Yes")) {
                    outstandingDialog(responseString);
                    return;
                }


                if (responseString != null && !responseString.equals("")) {

                    String action = generalFunc.getJsonValue(Utils.action_str, responseString);
                    if (action.equals("1")) {

                        if (generalFunc.getJsonValue("WebviewPayment", responseString).equalsIgnoreCase("Yes")) {

                            Bundle bn = new Bundle();
                            String url = generalFunc.getJsonValue(Utils.message_str, responseString);


                            bn.putString("url", url);
                            bn.putBoolean("handleResponse", true);
                            bn.putBoolean("isBack", false);
                            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
                            return;

                        }

                        iOrderId = generalFunc.getJsonValue("iOrderId", responseString);
                        iOrderId = generalFunc.getJsonValue("iOrderId", responseString);
                        Bundle bn = new Bundle();
                        bn.putString("iOrderId", iOrderId);
                        bn.putString("iServiceId", generalFunc.getServiceId());

                        bn.putString("eTakeAway", eTakeAway);
                        new ActUtils(getActContext()).startActWithData(OrderPlaceConfirmActivity.class, bn);


                    } else {

                        iOrderId = generalFunc.getJsonValue("iOrderId", responseString);
                        if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LOW_WALLET_AMOUNT")) {


                            String walletMsg = "";
                            String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                            if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                                walletMsg = low_balance_content_msg;
                            } else {
                                walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                            }


                            generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("No") ? generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN") :
                                    generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("NO") ? generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT") :
                                    "", button_Id -> {
                                if (button_Id == 1) {

                                    new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                                } else if (button_Id == 0) {
                                    if (generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("No")) {
                                        eWalletIgnore = "Yes";
                                        placeOrder();
                                    }
                                } else if (button_Id == 2) {
                                    iOrderId = "";
                                    ePaymentOption = "Cash";
                                    isWalletSelect = "No";
                                }
                            });


                            return;

                        }

                        if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_RESTAURANTS_CLOSE_NOTE")) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_RESTAURANTS_CLOSE_NOTE"));

                        } else if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_MENU_ITEM_NOT_AVAILABLE_TXT")) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_MENU_ITEM_NOT_AVAILABLE_TXT"));

                        } else if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_DELIVERY_LOCATION_NOT_ALLOW")) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELIVERY_LOCATION_NOT_ALLOW"));
                        } else {

                            String ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", responseString);
                            ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;
                            if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
                                closeuploadServicePicAlertBox();
                                String outstanding_restriction_label = generalFunc.getJsonValue(Utils.message_str, responseString);
                                if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                                    generateAlert.setCancelable(false);
                                    generateAlert.setBtnClickList(btn_id -> {
                                        if (btn_id == 1) {
                                            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                                        }
                                    });
                                    generateAlert.setContentMessage("", outstanding_restriction_label);
                                    generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));

                                    generateAlert.showAlertBox();
                                    return;
                                }
                            }
                            generalFunc.showGeneralMessage("", generalFunc.getJsonValue(Utils.message_str, responseString));

                        }


                    }
                } else {
                    generalFunc.showError();
                }
            });

        } catch (Exception e) {

        }

    }

    private void outstandingDialog(String responseString) {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);

        final MTextView outStandingTitle = dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = dialogView.findViewById(R.id.adjustTitleTxt);

        final LinearLayout cardArea = dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = dialogView.findViewById(R.id.adjustarea);
        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));

        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADJUST_OUT_AMT_ORDER_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));

        outStandingValue.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", responseString)));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));

        String ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", responseString);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        String ShowPayNow = generalFunc.getJsonValue("ShowPayNow", responseString);
        String ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", responseString);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;


        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            }*/
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }
        /*else if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {

            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                    }
                });
                generateAlert.setContentMessage("", outstanding_restriction_label);
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                if (ShowContactUsBtn != null && ShowContactUsBtn.equalsIgnoreCase("Yes")) {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                }
                generateAlert.showAlertBox();
                return;
            }
        }*/

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
            /*if (generalFunc.isRTLmode()) {
                (dialogView.findViewById(R.id.contactUsArrow)).setRotationY(180);
            }*/
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();

            Bundle bn = new Bundle();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + Utils.eSystem_Type +
                    "&ePaymentType=ChargeOutstandingAmount";

            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

            if (isContectLessPrefSelected) {
                url = url + "&isContactlessDelivery=Yes";
            }
            if (eTakeAway.equalsIgnoreCase("Yes")) {
                url = url + "&eTakeAway=Yes";
            }

            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
        });

        adjustarea.setOnClickListener(v -> {
            isAdjustPress = true;
            CheckOutOrderEstimateDetails(appliedPromoCode, false);
            outstanding_dialog.dismiss();
        });

        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);

        btn_type2.setOnClickListener(v -> outstanding_dialog.dismiss());

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    private void OrdePalceWithAudio() {
        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadVoiceDirectionFile");
            put("UserType", Utils.app_type);
            put("iMemberId", generalFunc.getMemberId());
            put("iUserId", generalFunc.getMemberId());
            put("MemberType", Utils.app_type);
            put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            put("GeneralUserType", Utils.app_type);
            put("GeneralMemberId", generalFunc.getMemberId());
            put("eSystem", Utils.eSystem_Type);
        }};

        new UploadProfileImage(CheckOutActivity.this, recordingItem1.getFilePath(), recordingItem1.getName(), paramsList, "UploadVoiceDirectionFile").execute();

    }

    private void setGeneralData() {
        JSONObject userProfileJsonObj = generalFunc.getJsonObject(userProfileJson);
        new SetGeneralData(generalFunc, userProfileJsonObj);
    }

    private void setPromoCode() {
        if (appliedPromoCode.equalsIgnoreCase("")) {
            defaultPromoView();
        } else {
            appliedPromoView();
        }
    }

    private void placeOrder() {
        if (internetConnection.isNetworkConnected()) {


            if (SITE_TYPE.equalsIgnoreCase("Demo")) {

                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NOTE_PLACE_ORDER_DEMO"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"), buttonId -> {
                    if (buttonId == 1) {

                        if (audioRecording != null && recordingItem1 != null) {
                            OrdePalceWithAudio();
                        } else {
                            orderPlaceCall();
                        }
                    }
                });
            } else {
                if (audioRecording != null && recordingItem1 != null) {
                    OrdePalceWithAudio();
                } else {
                    orderPlaceCall();
                }
            }
        } else {
            generalFunc.showError();

        }
    }

    @Override
    public void notifyProfileInfoInfo() {
        manageProfilePayment();
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        // Tip Features Handing of views clicks Start
        if (i == R.id.tipAmountArea1) {
            setSelected(tipAmountText1, tipAmountArea1);
        }
        if (i == R.id.tipAmountArea2) {
            setSelected(tipAmountText2, tipAmountArea2);
        }
        if (i == R.id.tipAmountArea3) {
            setSelected(tipAmountText3, tipAmountArea3);
        }
        if (i == R.id.closeImg1) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImg2) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImg3) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImgOther) {
            removeSelectedTip(true);
        }
        if (i == R.id.tipAmountAreaOther) {
            setSelected(tipAmountOtherText, tipAmountAreaOther);
        }
        if (i == R.id.iv_tip_help) {
            showTipInfoDialog(getActContext().getResources().getDrawable(R.drawable.ic_save_money), "LBL_DELIVERY_TIP_DESC");
        }
        if (i == R.id.tipGivenBtn) {
            tipAmount = "";

            final boolean tipAmountEntered = Utils.checkText(tipAmountBox) ? true : false;
            if (tipAmountEntered == false) {
                amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REQUIRED"));
                errorTxt.setVisibility(View.VISIBLE);
                return;
            } else {
                resetErrorTxt();

            }

            if (GeneralFunctions.parseDoubleValue(0, tipAmountBox.getText().toString()) > 0) {
                resetErrorTxt();
                setSelected(tipAmountOtherText, tipAmountAreaOther);

            } else {
                amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_GRATER_ZERO_VALUE_TXT"));
                errorTxt.setVisibility(View.VISIBLE);

            }

        }
        // Tip Features Handing of views clicks end
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == submitBtnId) {
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {
                getUserProfileJson();
                if (ePaymentOption.equalsIgnoreCase("")) {

                    generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_CHOOSE_PAYMENT_METHOD"));
                    return;
                }

                  /*  if (APP_PAYMENT_MODE.equalsIgnoreCase("Cash") && !checkboxWallet.isChecked() && casenote.getVisibility()==View.VISIBLE) {
                        generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_COD_NOT_AVAILABLE_TXT"));
                        return;
                    }
*/
                if (!restaurantstatus.equalsIgnoreCase("") && restaurantstatus.equalsIgnoreCase("closed")) {
                    generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_RESTAURANTS_CLOSE_NOTE"));
                    return;
                }


                boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, generalFunc.getJsonValue("vEmail", userProfileJson));
                if (generalFunc.getJsonValue("vPhone", userProfileJson).equals("") || (generalFunc.getJsonValue("vEmail", userProfileJson).equals("") && !isEmailBlankAndOptional)) {
                    //open account verification screen
                    if (generalFunc.getMemberId() != null && !generalFunc.getMemberId().equals("")) {
                        if (!generalFunc.getMemberId().equals("")) {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isbackshow", true);
                            new ActUtils(getActContext()).startActForResult(AccountverificationActivity.class, bn, Utils.SOCIAL_LOGIN_REQ_CODE);
                            return;
                        }
                    }
                }


                String ePhoneVerified = generalFunc.getJsonValue("ePhoneVerified", userProfileJson);
                if (!ePhoneVerified.equalsIgnoreCase("Yes")) {
                    notifyVerifyMobile();
                    return;
                }

                if (AddAddressArea.getVisibility() == View.VISIBLE) {
                    generalFunc.showMessage(btn_type2, generalFunc.retrieveLangLBl("", "LBL_SELECT_ADDRESS_TITLE_TXT"));
                    return;
                }


                Bundle bn = new Bundle();
                //  new ActUtils(getActContext()).startActForResult(BusinessSelectPaymentActivity.class, bn, Utils.SELECT_ORGANIZATION_PAYMENT_CODE);
                bn.putString("isWallet", isWalletSelect);
                bn.putBoolean("isCash", ePaymentOption.equals("Cash") ? true : false);

                boolean isDefaultSelected = false;
                for (int i1 = 0; i1 < currentSelectedItems.size(); i1++) {
                    if (currentSelectedItems.get(i1).get("eContactLess").equalsIgnoreCase("Yes")) {
                        isDefaultSelected = true;
                        break;
                    }
                }
                if (isPreference && isDefaultSelected) {
                    bn.putBoolean("isCODAllow", false);
                    bn.putBoolean("isPreference", true);
                } else {
                    bn.putBoolean("isCODAllow", isCODAllow);
                }
                bn.putBoolean("isCODAllow", isCODAllow);
                bn.putString("iServiceId", generalFunc.getServiceId());
                bn.putString("isCheckout", "");
                bn.putString("outStandingAmount", outStandingAmount);

                   /* if (ServiceModule.DeliverAllProduct) {
                        selectedMethod = "Instant";
                    } else {*/

//                    }
                bn.putString("eTakeAway", eTakeAway);
                String COMPANY_MINIMUM_ORDER = generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER);
                if (COMPANY_MINIMUM_ORDER != null && !COMPANY_MINIMUM_ORDER.equalsIgnoreCase("0")) {
                    double minimumAmt = GeneralFunctions.parseDoubleValue(0, COMPANY_MINIMUM_ORDER);
                    Cart cartData = cartList.get(0);
                    CurrencySymbol = cartData.getCurrencySymbol();
                    if (finalTotal < minimumAmt) {

                        generalFunc.showMessage(backImgView, LBL_MINIMUM_ORDER_NOTE + " " + generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(minimumAmt)), CurrencySymbol, true));
                        return;
                    }
                }


                //manage age proof
                if (eProofUpload.equalsIgnoreCase("Yes")) {
                    //  clearMediaPlayer();
                    pauseMediaPlayer();
                    takeAndUploadPic(getActContext());

                    btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));

                } else {
                    //  clearMediaPlayer();
                    pauseMediaPlayer();
                    // new ActUtils(getActContext()).startActForResult(ProfilePaymentActivity.class, bn, Utils.SELECT_ORGANIZATION_PAYMENT_CODE);

                    placeOrder();


                }

//                    if (ePaymentOption.equalsIgnoreCase("card") && !ServiceModule.DeliverAllProduct && SYSTEM_PAYMENT_FLOW.equalsIgnoreCase("Method-1")) {
//                        if (!Utils.checkText(selectedMethod)) {
//                            generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("Choose payment method for card payment", "LBL_CHOOSE_PAY_ONLINE_METHOD"));
//                            return;
//
//                        }
//
//                        if (Utils.checkText(selectedMethod) && selectedMethod.equalsIgnoreCase("Manual") && cardNumTxt.getVisibility() == View.GONE) {
//                            useExistingCardLL.performClick();
//                            return;
//                        }
//
//                        if (selectedMethod.equalsIgnoreCase("Manual") && cardNumTxt.getVisibility() == View.VISIBLE && isCardValidated == false) {
//                            checkPaymentCard(isWalletSelect, true);
//                            return;
//                        }
//
//                        placeOrder();
//                    } else {
//                        placeOrder();
//                    }


            }
        } else if (i == editCartImageview.getId()) {
            if (isFromEditCard) {
                onBackPressed();
            } else {
                new ActUtils(getActContext()).startAct(EditCartActivity.class);
                finish();
            }
        } else if (i == couponCodeArea.getId()) {
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                showMessageWithAction(applyCouponVTxt, generalFunc.retrieveLangLBl("", "LBL_PROMO_CODE_LOGIN_HINT"));
            } else {
                Bundle bn = new Bundle();
                bn.putString("CouponCode", appliedPromoCode);
                bn.putString("eSystem", Utils.eSystem_Type);
                bn.putString("iCompanyId", cartList.get(0).getiCompanyId());

                HashMap<String, String> data = new HashMap<>();
                data.put(Utils.SELECT_ADDRESS_ID, "");
                data.put(Utils.SELECT_LATITUDE, "");
                data.put(Utils.SELECT_LONGITUDE, "");
                data = generalFunc.retrieveValue(data);

                bn.putString("iUserAddressId", data.get(Utils.SELECT_ADDRESS_ID));

                bn.putString("vSourceLatitude", data.get(Utils.SELECT_LATITUDE));
                bn.putString("vSourceLongitude", data.get(Utils.SELECT_LONGITUDE));

                bn.putString("vDestLatitude", data.get(Utils.SELECT_LATITUDE));
                bn.putString("vDestLongitude", data.get(Utils.SELECT_LONGITUDE));

                bn.putString("eTakeAway", eTakeAway);
                new ActUtils(getActContext()).startActForResult(CouponActivity.class, bn, Utils.SELECT_COUPON_REQ_CODE);
            }
        } else if (i == editLocation.getId()) {

            Bundle bn = new Bundle();
            bn.putString("iCompanyId", cartList.get(0).getiCompanyId());
            bn.putString("addressid", generalFunc.retrieveValue(Utils.SELECT_ADDRESS_ID));
            bn.putString("eSystem", Utils.eSystem_Type);
            new ActUtils(getActContext()).startActForResult(ListAddressActivity.class, bn, SEL_ADDRESS);
        } else if (i == R.id.addAddressBtn) {
            Bundle bn = new Bundle();
            bn.putString("iCompanyId", cartList.get(0).getiCompanyId());
            new ActUtils(getActContext()).startActForResult(AddAddressActivity.class, bn, ADD_ADDRESS);
        } else if (i == R.id.couponCodeCloseImgView) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CONFIRM_COUPON_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                if (buttonId == 1) {
                    appliedPromoCode = "";
                    CheckOutOrderEstimateDetails("", false);
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_COUPON_REMOVE_SUCCESS"));
                }
            });

        } else if (i == btn_clearcart.getId()) {

            Realm realm = MyApp.getRealmInstance();
            generalFunc.removeAllRealmData(realm);
            Bundle bn = new Bundle();
            bn.putBoolean("isRestart", true);
            new ActUtils(getActContext()).startActWithData(EditCartActivity.class, bn);

        } else if (i == rightImgView.getId()) {
            Bundle bn = new Bundle();
            bn.putBoolean("isBack", true);
            // new ActUtils(mContext).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
            new ActUtils(getActContext()).startActWithData(PrescriptionActivity.class, bn);
        } else if (i == payArea.getId()) {

            Bundle bn = new Bundle();
            bn.putBoolean("handleResponse", true);
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + Utils.eSystem_Type;


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

            if (isContectLessPrefSelected) {
                url = url + "&isContactlessDelivery=Yes";
            }
            if (eTakeAway.equalsIgnoreCase("Yes")) {
                url = url + "&eTakeAway=Yes";
            }


            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

        }
    }


    ///  Tip Feature - Functions Start
    private void removeSelectedTip(boolean isCallFareEstimate) {
        tipAmount = "";
        tipAmountBox.setText("");
        tipAmountArea1.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountArea2.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountArea3.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountAreaOther.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountText1.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountText2.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountText3.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountOtherText.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        closeImg1.setVisibility(View.GONE);
        closeImg2.setVisibility(View.GONE);
        closeImg3.setVisibility(View.GONE);
        closeImgOther.setVisibility(View.GONE);
        if (!isPercentage) {
            tipAmountOtherText.setTag("");
        }
        tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        addTipArea.setVisibility(View.GONE);
        resetErrorTxt();
        if (isCallFareEstimate)
            CheckOutOrderEstimateDetails(appliedPromoCode, false);
    }

    private void setSelected(MTextView selectedTextAreaTxtView, LinearLayout selectedArea) {
        tipAmount = selectedTextAreaTxtView.getTag().toString();
        addTipArea.setVisibility(selectedArea == tipAmountAreaOther ? View.VISIBLE : View.GONE);
        resetErrorTxt();
        errorTxt.setVisibility(selectedArea == tipAmountAreaOther ? View.VISIBLE : View.GONE);
        tipAmountArea1.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea1 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg1.setVisibility(selectedArea == tipAmountArea1 ? View.VISIBLE : View.GONE);
        tipAmountArea2.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea2 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg2.setVisibility(selectedArea == tipAmountArea2 ? View.VISIBLE : View.GONE);
        tipAmountArea3.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea3 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg3.setVisibility(selectedArea == tipAmountArea3 ? View.VISIBLE : View.GONE);
        tipAmountAreaOther.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountAreaOther ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImgOther.setVisibility(selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox)) ? View.VISIBLE : View.GONE);

        tipAmountText1.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountText2.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountText3.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        tipAmountOtherText.setTextColor(ContextCompat.getColor(getActContext(), R.color.black));
        selectedTextAreaTxtView.setTextColor(ContextCompat.getColor(getActContext(), R.color.white));

        closeImg1.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), selectedArea == tipAmountArea1 ? R.color.white : R.color.black)));
        closeImg2.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), selectedArea == tipAmountArea2 ? R.color.white : R.color.black)));
        closeImg3.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), selectedArea == tipAmountArea3 ? R.color.white : R.color.black)));
        closeImgOther.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox)) ? R.color.white : R.color.black)));

        if (selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox))) {
            if (!isPercentage) {
                tipAmountOtherText.setTag(Utils.getText(tipAmountBox));
                tipAmount = Utils.getText(tipAmountBox);

            }
            tipAmountOtherText.setText(currencySymbol + Utils.getText(tipAmountBox));
            closeImgOther.setVisibility(View.VISIBLE);
            addTipArea.setVisibility(View.GONE);
            resetErrorTxt();
            CheckOutOrderEstimateDetails(appliedPromoCode, false);
        } else if (selectedArea != tipAmountAreaOther) {
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }

            tipAmountBox.setText("");
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
            CheckOutOrderEstimateDetails(appliedPromoCode, false);
        } else if ((selectedArea == tipAmountAreaOther && !Utils.checkText(Utils.getText(tipAmountBox))) && Utils.checkText(tipAmount)) {
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }
            tipAmountBox.setText("");
            tipAmount = "";
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
            CheckOutOrderEstimateDetails(appliedPromoCode, false);
        } else {
            tipAmountBox.setText("");
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        }

        if (selectedTextAreaTxtView == tipAmountText1) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText1.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText2) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText2.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText3) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText3.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountOtherText) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountOtherText.setTextColor(getResources().getColor(R.color.black));
        }
    }

    private void resetErrorTxt() {
        errorTxt.setText("");
        errorTxt.setVisibility(View.GONE);
        amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrectwithdesign));
    }

    private void showTipInfoDialog(Drawable drawable, String LBL_DATA) {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.design_tip_help, null);
        builder.setView(dialogView);


        final ImageView iamage_source = (ImageView) dialogView.findViewById(R.id.iamage_source);
        if (drawable != null) {
            iamage_source.setImageDrawable(drawable);
        }
        final MTextView okTxt = (MTextView) dialogView.findViewById(R.id.okTxt);
        final MTextView titileTxt = (MTextView) dialogView.findViewById(R.id.titileTxt);
        final MTextView msgTxt = (MTextView) dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("Tip your delivery partner", "LBL_TIP_DIALOG_TITLE"));
        okTxt.setText(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
        msgTxt.setText("" + generalFunc.retrieveLangLBl("You can set a delivery preference before the delivery agent attempts to deliver your package at your door. Your preferences will be taken care by delivery driver.", LBL_DATA));
        msgTxt.setMovementMethod(new ScrollingMovementMethod());
        okTxt.setOnClickListener(v -> giveTipAlertDialog.dismiss());
        giveTipAlertDialog = builder.create();
        giveTipAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(giveTipAlertDialog);
        giveTipAlertDialog.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        giveTipAlertDialog.show();


    }
///  Tip Feature - Functions End

    Snackbar snackbar = null;

    public void showMessageWithAction(View view, String message) {


        snackbar = Snackbar
                .make(view, message, Snackbar.LENGTH_INDEFINITE).setAction(generalFunc.retrieveLangLBl("Dismiss", "LBL_dismiss"), view1 -> snackbar.dismiss());
        snackbar.setActionTextColor(getActContext().getResources().getColor(R.color.verfiybtncolor));
        snackbar.setDuration(10000);
        snackbar.show();

    }

    androidx.appcompat.app.AlertDialog giveTipAlertDialog;

    private void showPreferenceHelp(Drawable drawable, String LBL_DATA, String title) {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.desgin_preference_help, null);
        builder.setView(dialogView);


        final ImageView iamage_source = (ImageView) dialogView.findViewById(R.id.iamage_source);
        if (drawable != null) {
            iamage_source.setImageDrawable(drawable);
        }
        final MButton okTxt = ((MaterialRippleLayout) dialogView.findViewById(R.id.okTxt)).getChildView();
        final MTextView titileTxt = (MTextView) dialogView.findViewById(R.id.titileTxt);
        final WKWebView msgTxt = (WKWebView) dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("User Preferences", "LBL_USER_PREF"));
        if (!title.equalsIgnoreCase("")) {
            titileTxt.setText(title);
        }
        okTxt.setText(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT "));

        msgTxt.loadData(generalFunc.retrieveLangLBl("You can set a delivery preference before the delivery agent attempts to deliver your package at your door. Your preferences will be taken care by delivery driver.", LBL_DATA), WKWebView.ContentType.ALERT_DIALOG);

        okTxt.setOnClickListener(v -> giveTipAlertDialog.dismiss());
        giveTipAlertDialog = builder.create();
        giveTipAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(giveTipAlertDialog);
        giveTipAlertDialog.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        giveTipAlertDialog.show();


    }


    private void takeAndUploadPic(final Context mContext) {
        selectedImagePath = "";

        uploadServicePicAlertBox = new Dialog(mContext, R.style.Theme_Dialog);
        uploadServicePicAlertBox.requestWindowFeature(Window.FEATURE_NO_TITLE);
        uploadServicePicAlertBox.setCancelable(true);
        uploadServicePicAlertBox.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
        uploadServicePicAlertBox.setContentView(R.layout.design_upload_service_pic);

        MTextView titleTxt = uploadServicePicAlertBox.findViewById(R.id.titleTxt);
        MTextView uploadTitleTxt = uploadServicePicAlertBox.findViewById(R.id.uploadTitleTxt);
        ImageView backImgView = uploadServicePicAlertBox.findViewById(R.id.backImgView);

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView skipTxt = uploadServicePicAlertBox.findViewById(R.id.skipTxt);
        MTextView noteTxt = uploadServicePicAlertBox.findViewById(R.id.noteTxt);
        MTextView noteLbl = uploadServicePicAlertBox.findViewById(R.id.noteLbl);
        final ImageView uploadImgVIew = uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew);
        LinearLayout uploadImgArea = uploadServicePicAlertBox.findViewById(R.id.uploadImgArea);
        MButton btn_type2 = ((MaterialRippleLayout) uploadServicePicAlertBox.findViewById(R.id.btn_type2)).getChildView();

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_IDENTIFICATION"));
        skipTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT"));
        skipTxt.setVisibility(View.GONE);

        noteLbl.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE"));
        noteTxt.setText(tProofNote);


        uploadTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_ID_IMAGE"));
        btn_type2.setText(generalFunc.retrieveLangLBl("Save Photo", "LBL_BTN_NEXT_TXT"));

        btn_type2.setId(Utils.generateViewId());
        btn_type2.setTextSize(16);
        uploadImgArea.setOnClickListener(view -> getFileSelector().openFileSelection(FileSelector.FileType.Image));
        btn_type2.setOnClickListener(view -> {
            if (TextUtils.isEmpty(selectedImagePath)) {
                generalFunc.showMessage(noteTxt, generalFunc.retrieveLangLBl("Please select image", "LBL_PLEASE_SELECT_IMAGE"));
            } else {
                uploadProof();
            }
        });

        skipTxt.setOnClickListener(view -> {
            selectedImagePath = "";
            uploadImgVIew.setImageURI(null);
        });
        backImgView.setOnClickListener(v -> closeuploadServicePicAlertBox());
        LayoutDirection.setLayoutDirection(uploadServicePicAlertBox);
        uploadServicePicAlertBox.show();
    }

    private void closeuploadServicePicAlertBox() {
        if (uploadServicePicAlertBox != null) {
            uploadServicePicAlertBox.dismiss();
        }
    }

    private void uploadProof() {
        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadIdProof");
            put("UserType", Utils.app_type);
            put("iMemberId", generalFunc.getMemberId());
            put("iUserId", generalFunc.getMemberId());
            put("MemberType", Utils.app_type);
            put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            put("GeneralUserType", Utils.app_type);
            put("GeneralMemberId", generalFunc.getMemberId());
            put("eSystem", Utils.eSystem_Type);
        }};

        new UploadProfileImage(CheckOutActivity.this, selectedImagePath, Utils.TempProfileImageName, paramsList).execute();

    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        super.onFileSelected(mFileUri, mFilePath, mFileType);
        selectedImagePath = mFilePath;
        try {
            BitmapFactory.Options options = new BitmapFactory.Options();
            options.inJustDecodeBounds = true;
            BitmapFactory.decodeFile(selectedImagePath, options);

            /*int imageHeight = options.outHeight;
            int imageWidth = options.outWidth;

            double ratioOfImage = (double) imageWidth / (double) imageHeight;
            double widthOfImage = ratioOfImage * Utils.dipToPixels(getActContext(), 200);*/

            new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        } catch (Exception e) {


            new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).build();


        }

        uploadServicePicAlertBox.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
        uploadServicePicAlertBox.findViewById(R.id.ic_add).setVisibility(View.GONE);
    }

    public void handleImgUploadResponse(String responseString, boolean isAudio) {

        if (responseString != null && !responseString.equals("")) {

            Logger.d("handleImgUploadResponse", "::" + responseString);
            if (isAudio) {
                iVoiceDirectionFileId = generalFunc.getJsonValue("iVoiceDirectionFileId", responseString);
                orderPlaceCall();
                return;
            }
            iIdProofImageId = generalFunc.getJsonValue("iIdProofImageId", responseString).toString();

            Bundle bn = new Bundle();
            //  new ActUtils(getActContext()).startActForResult(BusinessSelectPaymentActivity.class, bn, Utils.SELECT_ORGANIZATION_PAYMENT_CODE);
            bn.putString("isWallet", isWalletSelect);
            bn.putBoolean("isCash", ePaymentOption.equals("Cash"));

            boolean isDefaultSelected = false;
            for (int i1 = 0; i1 < currentSelectedItems.size(); i1++) {
                if (currentSelectedItems.get(i1).get("eContactLess").equalsIgnoreCase("Yes")) {
                    isDefaultSelected = true;
                    break;
                }
            }
            if (isPreference && isDefaultSelected) {
                bn.putBoolean("isCODAllow", false);
                bn.putBoolean("isPreference", true);
            } else {
                bn.putBoolean("isCODAllow", isCODAllow);
            }
            bn.putBoolean("isCODAllow", isCODAllow);
            bn.putString("iServiceId", generalFunc.getServiceId());
            bn.putString("isCheckout", "");
            bn.putString("outStandingAmount", outStandingAmount);

                   /* if (ServiceModule.DeliverAllProduct) {
                        selectedMethod = "Instant";
                    } else {*/

            bn.putString("eTakeAway", eTakeAway);
            String COMPANY_MINIMUM_ORDER = generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER);
            if (COMPANY_MINIMUM_ORDER != null && !COMPANY_MINIMUM_ORDER.equalsIgnoreCase("0")) {
                double minimumAmt = GeneralFunctions.parseDoubleValue(0, COMPANY_MINIMUM_ORDER);
                Cart cartData = cartList.get(0);
                CurrencySymbol = cartData.getCurrencySymbol();
                if (finalTotal < minimumAmt) {

                    generalFunc.showMessage(backImgView, LBL_MINIMUM_ORDER_NOTE + " " + generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(minimumAmt)), CurrencySymbol, true));
                    return;
                }
            }

            //   clearMediaPlayer();
            //  new ActUtils(getActContext()).startActForResult(ProfilePaymentActivity.class, bn, Utils.SELECT_ORGANIZATION_PAYMENT_CODE);
//            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + Utils.eSystem_Type;
//
//
//            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
//            url = url + "&GeneralUserType=" + Utils.app_type;
//            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
//            url = url + "&ePaymentOption=" + "Card";
//            url = url + "&vPayMethod=" + "Instant";
//            url = url + "&SYSTEM_TYPE=" + "APP";
//            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();
//
//
//            bn.putString("url", url);
//            bn.putBoolean("handleResponse", true);
//            bn.putBoolean("isBack", false);
//            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

            placeOrder();
        } else {
            generalFunc.showError();
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();

        if (audioRecording.mMediaPlayer != null && audioRecording.mMediaPlayer.isPlaying()) {
            clearMediaPlayer();
        }

    }
}
