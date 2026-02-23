package com.act.parking.fragment;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ParkingReviewsFragmentLayoutBinding;
import com.act.parking.ParkingDetailsActivity;
import com.act.parking.adapter.ParkingReviewsAdapter;

public class ParkingReviewsFragment extends BaseFragment {

    public ParkingReviewsFragmentLayoutBinding binding;
    private ParkingDetailsActivity mActivity;
    private GeneralFunctions generalFunc;
    private ParkingReviewsAdapter reviewAdapter;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.parking_reviews_fragment_layout, container, false);

        initialize();

        return binding.getRoot();
    }

    private void initialize() {

        reviewAdapter = new ParkingReviewsAdapter(mActivity, generalFunc, mActivity.reviewsData, (position, mapData) -> {
        });
        binding.reviewsRv.setAdapter(reviewAdapter);

    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof ParkingDetailsActivity) {
            mActivity = (ParkingDetailsActivity) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

}
