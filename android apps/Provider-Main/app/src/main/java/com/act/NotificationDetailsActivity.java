package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityNotificationDetailsBinding;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import java.util.HashMap;

public class NotificationDetailsActivity extends ParentActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        ActivityNotificationDetailsBinding binding = DataBindingUtil.setContentView(this, R.layout.activity_notification_details);

        HashMap<String, String> list = (HashMap<String, String>) getIntent().getSerializableExtra("data");

        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        String eType = list.get("eType");
        String label = eType.equalsIgnoreCase("Notification") ? "LBL_NOTIFICATIONS" : "LBL_NEWS";
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl(eType, label));

        String vImage = list.get("vImage");
        if (Utils.checkText(vImage)) {
            binding.imgArea.setVisibility(View.VISIBLE);

            new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), vImage, (int) (Utils.getScreenPixelWidth(getActContext()) - Utils.dipToPixels(getActContext(), 20)), 0)), binding.newsImage).build();
        } else {
            binding.imgArea.setVisibility(View.GONE);
        }


        binding.notificationTitleTxt.setText(list.get("vTitle"));
        //binding.dateTxt.setText(list.get("dDateTime"));
        binding.dateTxt.setText(list.get("tDisplayDateTime"));
        binding.detailsTxt.setText(list.get("tDescription"));
    }

    private Context getActContext() {
        return NotificationDetailsActivity.this;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        if (view.getId() == R.id.backImgView) {
            super.onBackPressed();
        }
    }
}