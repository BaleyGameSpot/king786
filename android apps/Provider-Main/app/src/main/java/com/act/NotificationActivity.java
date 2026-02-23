package com.act;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.fragments.NotiFicationFragment;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityNotificationBinding;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;

public class NotificationActivity extends ParentActivity {

    private ActivityNotificationBinding binding;

    CharSequence[] titles;
    ArrayList<Fragment> fragmentList = new ArrayList<>();


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_notification);

        String LBL_NOTIFICATIONS = generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS");

        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(LBL_NOTIFICATIONS);


        if (generalFunc.getJsonValueStr("ENABLE_NEWS_SECTION", obj_userProfile).equalsIgnoreCase("Yes")) {

            if (generalFunc.isRTLmode()) {

                titles = new CharSequence[]{generalFunc.retrieveLangLBl("all", "LBL_NEWS"), LBL_NOTIFICATIONS, generalFunc.retrieveLangLBl("news", "LBL_ALL")};
                binding.tabLayoutArea.materialTabs.setVisibility(View.VISIBLE);

                fragmentList.add(generateNotificationFrag(Utils.News));
                fragmentList.add(generateNotificationFrag(Utils.Notificatons));
                fragmentList.add(generateNotificationFrag(Utils.All));

            } else {
                titles = new CharSequence[]{generalFunc.retrieveLangLBl("all", "LBL_ALL"), LBL_NOTIFICATIONS, generalFunc.retrieveLangLBl("news", "LBL_NEWS")};
                binding.tabLayoutArea.materialTabs.setVisibility(View.VISIBLE);
                fragmentList.add(generateNotificationFrag(Utils.All));
                fragmentList.add(generateNotificationFrag(Utils.Notificatons));
                fragmentList.add(generateNotificationFrag(Utils.News));
            }

        } else {
            titles = new CharSequence[]{LBL_NOTIFICATIONS};
            binding.tabLayoutArea.materialTabs.setVisibility(View.GONE);
            fragmentList.add(generateNotificationFrag(Utils.Notificatons));
        }

        binding.viewPager.setAdapter(new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList));
        binding.tabLayoutArea.materialTabs.setupWithViewPager(binding.viewPager);

        binding.viewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                fragmentList.get(position).onResume();
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });
    }

    public NotiFicationFragment generateNotificationFrag(String type) {
        NotiFicationFragment frag = new NotiFicationFragment();
        Bundle bn = new Bundle();
        bn.putString("type", type);
        frag.setArguments(bn);
        return frag;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        if (view.getId() == R.id.backImgView) {
            super.onBackPressed();
        }
    }
}