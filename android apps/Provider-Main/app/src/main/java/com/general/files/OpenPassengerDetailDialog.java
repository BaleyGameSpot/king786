package com.general.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.appcompat.app.AlertDialog;

import com.buddyverse.providers.R;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.simpleratingbar.SimpleRatingBar;

import java.util.HashMap;
import java.util.Objects;

public class OpenPassengerDetailDialog {

    Context mContext;
    HashMap<String, String> data_trip;
    GeneralFunctions generalFunc;

    AlertDialog mAlertDialog;

    ProgressBar LoadingProgressBar;
    boolean isNotification;
    private final DialogListener listener;

    public OpenPassengerDetailDialog(Context mContext, HashMap<String, String> data_trip, GeneralFunctions generalFunc, boolean isNotification, DialogListener dialogListener) {
        this.mContext = mContext;
        this.data_trip = data_trip;
        this.generalFunc = generalFunc;
        this.isNotification = isNotification;
        this.listener = dialogListener;

        show();
    }

    private void show() {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        builder.setTitle("");

        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.design_passenger_detail_dialog, null);
        builder.setView(dialogView);

        LoadingProgressBar = dialogView.findViewById(R.id.LoadingProgressBar);

        LinearLayout msgArea = dialogView.findViewById(R.id.msgArea);
        boolean isKiosk = data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk");
        boolean isUser = Utils.checkText(data_trip.get("iGcmRegId_U"));
        /*if (isKiosk || !isUser) {
            msgArea.setVisibility(View.GONE);
        }*/

        ((MTextView) dialogView.findViewById(R.id.rateTxt)).setText(generalFunc.convertNumberWithRTL(data_trip.get("PRating")));
        ((MTextView) dialogView.findViewById(R.id.nameTxt)).setText(data_trip.get("PName"));

        ImageView cancelUpload = dialogView.findViewById(R.id.cancelUpload);
        cancelUpload.setOnClickListener(v -> {
            if (mAlertDialog != null) {
                mAlertDialog.dismiss();
            }
        });

        String msg;
        if (Objects.requireNonNull(data_trip.get("REQUEST_TYPE")).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            msg = generalFunc.retrieveLangLBl("", "LBL_USER_DETAIL");
        } else {
            msg = generalFunc.retrieveLangLBl("", "LBL_PASSENGER_DETAIL");
        }

        ((MTextView) dialogView.findViewById(R.id.passengerDTxt)).setText(msg);
        ((MTextView) dialogView.findViewById(R.id.callTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        ((MTextView) dialogView.findViewById(R.id.msgTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MESSAGE_TXT"));
        ((SimpleRatingBar) dialogView.findViewById(R.id.ratingBar)).setRating(GeneralFunctions.parseFloatValue(0, data_trip.get("PRating")));

        String image_url = CommonUtilities.USER_PHOTO_PATH + data_trip.get("PassengerId") + "/"
                + data_trip.get("PPicName");

        new LoadImage.builder(LoadImage.bind(image_url), ((SelectableRoundedImageView) dialogView.findViewById(R.id.passengerImgView))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        (dialogView.findViewById(R.id.callArea)).setOnClickListener(view -> {
            if (mAlertDialog != null) {
                mAlertDialog.dismiss();
            }
            listener.callClick();
        });

        msgArea.setOnClickListener(view -> {
            if (mAlertDialog != null) {
                mAlertDialog.dismiss();
            }
            listener.msgClick();
        });

        (dialogView.findViewById(R.id.closeImg)).setOnClickListener(view -> {
            if (mAlertDialog != null) {
                mAlertDialog.dismiss();
            }
        });

        mAlertDialog = builder.create();
        LayoutDirection.setLayoutDirection(mAlertDialog);
        mAlertDialog.getWindow().setBackgroundDrawableResource(android.R.color.transparent);
        mAlertDialog.show();
        if (isNotification) {
            isNotification = false;
            msgArea.performClick();
        }
    }

    public interface DialogListener {
        void callClick();

        void msgClick();
    }
}