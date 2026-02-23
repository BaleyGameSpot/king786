package com.dialogs;


import android.content.Context;
import android.graphics.Color;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;

import androidx.appcompat.widget.AppCompatImageView;

import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;

import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.utils.LayoutDirection;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class ImageTitleDialog {

    static BottomSheetDialog optionDailog;
    static GeneralFunctions generalFunc;
    static ImageView closeImg;
    static AppCompatImageView image;
    static MTextView titleTxt, subTitleTxt;
    static Context mContext;
    static InternetConnection intCheck;
    static MButton btn_type1;


    public static void build(Context mContext, String title, String subTitle, String btnTxt, int imglocalPath) {

        ImageTitleDialog.mContext = mContext;
        generalFunc = new GeneralFunctions(mContext);
        optionDailog = new BottomSheetDialog(mContext);
        intCheck = new InternetConnection(mContext);

        View contentView = View.inflate(mContext, R.layout.layout_image_title, null);

        optionDailog.setContentView(contentView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT,
                mContext.getResources().getDimensionPixelSize(R.dimen._400sdp)));
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(mContext.getResources().getDimensionPixelSize(R.dimen._400sdp));
        optionDailog.setCancelable(false);

        btn_type1 = ((MaterialRippleLayout) contentView.findViewById(R.id.btn_type1)).getChildView();
        btn_type1.setId(Utils.generateViewId());
        btn_type1.setOnClickListener(new setOnClickList());
        btn_type1.setText(btnTxt);
        closeImg = contentView.findViewById(R.id.closeImg);
        image = contentView.findViewById(R.id.image);
        titleTxt = contentView.findViewById(R.id.titleTxt);
        subTitleTxt = contentView.findViewById(R.id.subtitleTxt);
        titleTxt.setText(title);
        subTitleTxt.setText(subTitle);
        closeImg.setOnClickListener(new setOnClickList());
        new LoadImageGlide.builder(mContext, LoadImageGlide.bind(imglocalPath), image).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        ((View) contentView.getParent()).setBackgroundColor(Color.TRANSPARENT);
        Animation a = AnimationUtils.loadAnimation(mContext, R.anim.bottom_up);
        a.reset();
        contentView.clearAnimation();
        contentView.startAnimation(a);

        LayoutDirection.setLayoutDirection(optionDailog);
        optionDailog.show();


    }


    public static class setOnClickList implements View.OnClickListener {

        @Override
        public void onClick(View view) {

            int i = view.getId();
            optionDailog.dismiss();
            if (i == btn_type1.getId()) {
                optionDailog.dismiss();
            }


        }
    }


}