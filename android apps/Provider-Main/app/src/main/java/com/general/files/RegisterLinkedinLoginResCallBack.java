package com.general.files;

import android.content.Context;

import com.act.MobileStegeActivity;
import com.view.MyProgressDialog;


/**
 * Created by Admin on 29-06-2016.
 */
public class RegisterLinkedinLoginResCallBack {
    Context mContext;
    GeneralFunctions generalFunc;

    MyProgressDialog myPDialog;
    MobileStegeActivity appLoginAct;

    public RegisterLinkedinLoginResCallBack(Context mContext) {
        this.mContext = mContext;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        appLoginAct = (MobileStegeActivity ) mContext;

    }


    public void continueLogin() {
        OpenLinkedinDialog openLinkedinDialog = new OpenLinkedinDialog(mContext, generalFunc);

    }


}
