package com.general.files;

import android.content.Context;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.LinearLayout;

import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.WKWebView;

public class ToolTipDialog implements WKWebView.WebClient {

    BottomSheetDialog tipDialog;
    Context context;
    GeneralFunctions generalFunctions;
    String Title;
    String Msg;
    BottomSheetBehavior mBehavior;
    MTextView titleTxt;
    LinearLayout imageArea;

    public ToolTipDialog(Context context, GeneralFunctions generalFunctions, String Title, String Msg) {
        this.context = context;
        this.generalFunctions = generalFunctions;
        this.Title = Title;
        this.Msg = Msg;
        createView();
    }

    public void createView() {
        if (tipDialog != null && tipDialog.isShowing()) {
            return;
        }
        tipDialog = new BottomSheetDialog(context);

        View contentView = View.inflate(context, R.layout.desgin_tooltip, null);

        tipDialog.setContentView(contentView);

        tipDialog.setCancelable(true);

        View bottomSheetView = tipDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(context.getResources().getColor(android.R.color.transparent));

        mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setHideable(true);
        mBehavior.setDraggable(false);
        titleTxt = contentView.findViewById(R.id.titleTxt);
        imageArea = contentView.findViewById(R.id.imageArea);
        MButton okTxt = ((MaterialRippleLayout) contentView.findViewById(R.id.okTxt)).getChildView();
        okTxt.setId(Utils.generateViewId());
        WKWebView msgTxt = contentView.findViewById(R.id.msgTxt);
        msgTxt.setBackgroundColor(context.getResources().getColor(R.color.cardView23ProBG));

        titleTxt.setText(Title);
        okTxt.setText(generalFunctions.retrieveLangLBl("", "LBL_OK_THANKS"));
        okTxt.setOnClickListener(v -> tipDialog.dismiss());
        msgTxt.loadData(Msg, WKWebView.ContentType.ALERT_DIALOG);
        msgTxt.setWebClient(this);
        LayoutDirection.setLayoutDirection(tipDialog);
        tipDialog.show();
    }

    @Override
    public void onPageFinished(WKWebView view, String url) {
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (view.getContentHeight() + titleTxt.getMeasuredHeight() + imageArea.getMeasuredHeight() + context.getResources().getDimensionPixelSize(R.dimen._15sdp) >= (Utils.getScreenPixelHeight(context) / 3)) {
                LinearLayout.LayoutParams lyParams = (LinearLayout.LayoutParams) view.getLayoutParams();
                lyParams.height = (int) (Utils.getScreenPixelHeight(context) / 2);
                view.setLayoutParams(lyParams);
                mBehavior.setPeekHeight((int) (Utils.getScreenPixelHeight(context) * 95) / 100);
            }
        }, 200);
    }
}