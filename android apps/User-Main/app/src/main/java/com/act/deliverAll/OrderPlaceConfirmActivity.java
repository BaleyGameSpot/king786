package com.act.deliverAll;

import android.animation.Animator;
import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.os.Handler;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.MyApp;
import com.act.BookingActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityOrderPlaceConfirmBinding;
import com.model.ServiceModule;
import com.realmModel.Cart;
import com.utils.Utils;

import io.realm.Realm;

public class OrderPlaceConfirmActivity extends ParentActivity {

    private ActivityOrderPlaceConfirmBinding binding;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_order_place_confirm);

        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        realm.delete(Cart.class);
        realm.commitTransaction();

        binding.placeOrderTitle.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_PLACED"));
        if (getIntent().getStringExtra("eTakeAway") != null && getIntent().getStringExtra("eTakeAway").equalsIgnoreCase("Yes")) {
            binding.placeOrderNote.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_PLACE_MSG_TAKE_AWAY"));
        } else {
            binding.placeOrderNote.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_PLACE_MSG"));
        }
        binding.lottieAnim.addAnimatorListener(new Animator.AnimatorListener() {
            @Override
            public void onAnimationStart(@NonNull Animator animator) {
            }

            @Override
            public void onAnimationEnd(@NonNull Animator animator) {
                new Handler().postDelayed(OrderPlaceConfirmActivity.this::moveToTrackOrder, 2000);
            }

            @Override
            public void onAnimationCancel(@NonNull Animator animator) {

            }

            @Override
            public void onAnimationRepeat(@NonNull Animator animator) {

            }
        });

    }

    @Override
    protected void onResume() {
        super.onResume();
        if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && generalFunc != null && !ServiceModule.isDeliverAllOnly()) {
            generalFunc.removeValue(Utils.iServiceId_KEY);
        }
    }

    @Override
    public void onBackPressed() {
        //super.onBackPressed();
    }

    private void moveToTrackOrder() {
        Bundle bn = new Bundle();
        bn.putBoolean("isOrder", true);
        bn.putString("iOrderId", getIntent().getStringExtra("iOrderId"));
        bn.putString("iServiceId", getIntent().getStringExtra("iServiceId"));
        new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
        finish();
    }

    private Context getActContext() {
        return OrderPlaceConfirmActivity.this;
    }
}