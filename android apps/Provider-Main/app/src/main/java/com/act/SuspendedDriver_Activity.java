package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class SuspendedDriver_Activity extends ParentActivity {


    MButton btn_type2;
    int submitBtnId;
    ImageView menuImgView;
    MTextView suspendedNote;
    ImageView menuImgRightView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_suspended_driver_);
        initView();
        setLabel();
    }

    private void initView() {
        menuImgView =  findViewById(R.id.menuImgView);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        menuImgRightView =  findViewById(R.id.menuImgRightView);
        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        suspendedNote =  findViewById(R.id.suspendedNote);
        menuImgView.setVisibility(View.GONE);
        addToClickHandler(menuImgRightView);
        menuImgRightView.setVisibility(View.VISIBLE);
    }

    public Context getActContext() {
        return SuspendedDriver_Activity.this;
    }

    private void setLabel() {
        btn_type2.setText(generalFunc.retrieveLangLBl("Contact Us", "LBL_FOOTER_HOME_CONTACT_US_TXT"));
        suspendedNote.setText(generalFunc.retrieveLangLBl("Oops! Seems your account is Suspended.Kindly contact administrator.", "LBL_CONTACT_US_STATUS_SUSPENDED_DRIVER"));
    }


    public void onClick(View view) {

        int i = view.getId();

        if (i == submitBtnId) {

            new ActUtils(getActContext()).startAct(ContactUsActivity.class);

        } else if (i == menuImgRightView.getId()) {
            MyApp.getInstance().logOutFromDevice(false);
        }

    }

}
