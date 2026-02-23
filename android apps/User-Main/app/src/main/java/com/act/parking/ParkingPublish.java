package com.act.parking;

import android.annotation.SuppressLint;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.widget.ViewPager2;

import com.activity.ParentActivity;
import com.adapter.ViewPager2Adapter;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityPublishParkingBinding;
import com.act.parking.fragment.ParkingPublishStep1Fragment;
import com.act.parking.fragment.ParkingPublishStep2Fragment;
import com.act.parking.fragment.ParkingPublishStep3Fragment;
import com.act.parking.fragment.ParkingPublishStep4Fragment;
import com.act.parking.model.ParkingPublishData;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.Objects;

public class ParkingPublish extends ParentActivity {

    public ActivityPublishParkingBinding binding;
    public Toolbar mToolbar;
    private ViewPager2Adapter mViewPager2Adapter;
    public ParkingPublishData.LocationDetails parkingPublishLocationData;
    public ParkingPublishData parkingPublishData;
    private final ArrayList<Fragment> listOfFrag = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_publish_parking);
        initialization();
        mainDataSet();

    }

    private void initialization() {
        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        parkingPublishLocationData = new ParkingPublishData.LocationDetails();
        parkingPublishData = new ParkingPublishData();
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_RENT_YOUR_SPACE_TXT"));


        binding.rlProgress.setVisibility(View.GONE);

        binding.publishParkingBtn.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SUBMIT_BTN_TXT"));
        binding.publishParkingBtn.setVisibility(View.GONE);
        addToClickHandler(binding.previousBtn);
        addToClickHandler(binding.nextBtn);
        addToClickHandler(binding.rlProgress);
        addToClickHandler(binding.publishParkingBtn);
        if (generalFunc.isRTLmode()) {
            binding.previousBtn.setRotation(0);
            binding.nextBtn.setRotation(180);
        }

        mViewPager2Adapter = new ViewPager2Adapter(getSupportFragmentManager(), this.getLifecycle(), listOfFrag);
        binding.ParkingStepViewPager.setAdapter(mViewPager2Adapter);
        binding.ParkingStepViewPager.setUserInputEnabled(false);
        binding.ParkingStepViewPager.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                super.onPageSelected(position);
                setPagerHeight();
            }
        });

    }

    @SuppressLint("NotifyDataSetChanged")
    private void mainDataSet() {
        listOfFrag.clear();

        listOfFrag.add(new ParkingPublishStep1Fragment());
        listOfFrag.add(new ParkingPublishStep2Fragment());
        listOfFrag.add(new ParkingPublishStep3Fragment());
        listOfFrag.add(new ParkingPublishStep4Fragment());


        binding.ParkingStepViewPager.setOffscreenPageLimit(listOfFrag.size());
        Objects.requireNonNull(binding.ParkingStepViewPager.getAdapter()).notifyDataSetChanged();
        if (listOfFrag.size() >= 2) {
            binding.previousBtn.setVisibility(View.INVISIBLE);
            binding.nextBtn.setVisibility(View.VISIBLE);
            setToolSubTitle();
        }
    }

    @SuppressLint("SetTextI18n")
    private void setToolSubTitle() {
        int currItemPos = binding.ParkingStepViewPager.getCurrentItem();
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
            binding.publishParkingBtn.setVisibility(View.VISIBLE);
            binding.nextBtn.setVisibility(View.GONE);
        } else {
            binding.publishParkingBtn.setVisibility(View.GONE);
            binding.nextBtn.setVisibility(View.VISIBLE);
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void setPagePrevious() {
        Utils.hideKeyboard(this);
        binding.ParkingStepViewPager.setCurrentItem(binding.ParkingStepViewPager.getCurrentItem() - 1, true);
        setToolSubTitle();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void setPageNext() {
        Utils.hideKeyboard(this);

        int currItemPos = binding.ParkingStepViewPager.getCurrentItem();
        if (currItemPos == (listOfFrag.size() - 1)) {

            generalFunc.showMessage(binding.bottomAreaView, "Done");

        } else {
            binding.ParkingStepViewPager.setCurrentItem(currItemPos + 1, true);
            setToolSubTitle();
        }
    }


    public void setPagerHeight() {
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            Fragment fragment = mViewPager2Adapter.createFragment(binding.ParkingStepViewPager.getCurrentItem());
            View childView = fragment.getView();
            if (childView == null) return;

            int wMeasureSpec = View.MeasureSpec.makeMeasureSpec(childView.getWidth(), View.MeasureSpec.EXACTLY);
            int hMeasureSpec = View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED);
            childView.measure(wMeasureSpec, hMeasureSpec);

            LinearLayout.LayoutParams lyParams = (LinearLayout.LayoutParams) binding.ParkingStepViewPager.getLayoutParams();
            if (lyParams.height != childView.getMeasuredHeight()) {
                lyParams.height = childView.getMeasuredHeight();
                binding.ParkingStepViewPager.setLayoutParams(lyParams);
            }
        }, 200);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.previousBtn) {
            if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep3Fragment fragment) {
                fragment = (ParkingPublishStep3Fragment) listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem());
                fragment.checkPagePrevious();
            } else if (binding.previousBtn.getVisibility() == View.VISIBLE) {
                setPagePrevious();
            }
        } else if (i == R.id.nextBtn) {
            if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep1Fragment fragment) {
                fragment = (ParkingPublishStep1Fragment) listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem());
                fragment.checkPageNext();
            } else if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep2Fragment fragment) {
                fragment = (ParkingPublishStep2Fragment) listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem());
                fragment.checkPageNext();
            } else if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep3Fragment fragment) {
                fragment = (ParkingPublishStep3Fragment) listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem());
                fragment.checkPageNext();
            }

        } else if (i == R.id.publishParkingBtn) {
            if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep4Fragment fragment) {
                fragment.checkPageNext();
            }
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        super.onFileSelected(mFileUri, mFilePath, mFileType);
        if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep4Fragment) {
            ParkingPublishStep4Fragment fragment = (ParkingPublishStep4Fragment) listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem());
            binding.rlProgress.setVisibility(View.VISIBLE);
            fragment.configMedia(generalFunc, mFilePath, mFileType);
        }
    }


    public void handleImgUploadResponse(String responseString) {
        if (responseString != null && !responseString.equalsIgnoreCase("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                if (listOfFrag.get(binding.ParkingStepViewPager.getCurrentItem()) instanceof ParkingPublishStep4Fragment fragment) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), buttonId -> {
                        fragment.getDocumentsAndMedia();
                    });

                }
            }
        }
    }
}