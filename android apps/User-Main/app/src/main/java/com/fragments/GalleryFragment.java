package com.fragments;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.GridLayoutManager;

import com.adapter.files.GalleryImagesRecyclerAdapter;
import com.general.files.GeneralFunctions;
import com.act.MoreInfoActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentGalleryBinding;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

public class GalleryFragment extends BaseFragment {

    private FragmentGalleryBinding binding;
    private MoreInfoActivity mActivity;

    private GalleryImagesRecyclerAdapter galleryAdapter;
    private final ArrayList<HashMap<String, String>> listData = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_gallery, container, false);

        galleryAdapter = new GalleryImagesRecyclerAdapter(mActivity, listData, mActivity.generalFunc, false, false, false);
        binding.galleryRecyclerView.setAdapter(galleryAdapter);
        binding.galleryRecyclerView.setLayoutManager(new GridLayoutManager(getActivity(), galleryAdapter.getNumOfColumns()));
        galleryAdapter.setOnItemClickListener(new GalleryImagesRecyclerAdapter.OnItemClickListener() {
            @Override
            public void onItemClickList(View v, int position) {
                mActivity.openCarouselView(listData, position);
            }

            @Override
            public void onLongItemClickList(View v, int position) {

            }

            @Override
            public void onDeleteClick(View v, int position) {

            }
        });

        binding.noDataTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
        getImages();

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof MoreInfoActivity activity) {
            mActivity = activity;
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getImages() {
        binding.loadingImages.setVisibility(View.VISIBLE);
        binding.noDataTxt.setVisibility(View.GONE);
        listData.clear();

        galleryAdapter.notifyDataSetChanged();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getProviderImages");
        parameters.put("iDriverId", mActivity.getIntent().getStringExtra("iDriverId"));
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(mActivity, parameters, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                listData.clear();
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    MyUtils.createArrayListJSONArray(mActivity.generalFunc, listData, mActivity.generalFunc.getJsonArray(Utils.message_str, responseString));

                    if (listData.size() == 0) {
                        binding.noDataTxt.setVisibility(View.VISIBLE);
                    }
                } else {
                    binding.noDataTxt.setVisibility(View.VISIBLE);
                }
                galleryAdapter.notifyDataSetChanged();
            } else {
                mActivity.generalFunc.showError(true);
            }
            binding.loadingImages.setVisibility(View.GONE);
        });
    }
}