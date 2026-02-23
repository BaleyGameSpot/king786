package com.ViewPagerCards;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.cardview.widget.CardView;
import androidx.viewpager.widget.PagerAdapter;

import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Logger;

import java.util.ArrayList;
import java.util.List;

public class HomeCardPagerAdapter extends PagerAdapter implements CardAdapter {

    private int bannerHeight;
    private List<CardView> mViews;
    private List<String> mData;
    private float mBaseElevation;
    Context mContext;

    public HomeCardPagerAdapter() {
        mData = new ArrayList<>();
        mViews = new ArrayList<>();
    }

    public void addCardItem(String item, Context context, int bannerHeight) {
        mViews.add(null);
        mData.add(item);
        this.mContext = context;
        this.bannerHeight = bannerHeight;
    }

    public float getBaseElevation() {
        return mBaseElevation;
    }

    @Override
    public CardView getCardViewAt(int position) {
        return mViews.get(position);
    }

    @Override
    public int getCount() {
        return mData.size();
    }

    @Override
    public boolean isViewFromObject(View view, Object object) {
        return view == object;
    }

    @Override
    public Object instantiateItem(ViewGroup container, int position) {
        View view = LayoutInflater.from(container.getContext()).inflate(R.layout.homebanner_adapter, container, false);
        container.addView(view);
        CardView cardView = view.findViewById(R.id.cardView);
        ImageView imageView = view.findViewById(R.id.imagView);

        if (mBaseElevation == 0) {
            mBaseElevation = cardView.getCardElevation();
        }

        position = position % mViews.size();

        Logger.d("BannerimageURL_1", "::" + mData.get(position));

        CardView.LayoutParams bannerLayoutParams = (CardView.LayoutParams) imageView.getLayoutParams();
        bannerLayoutParams.height = bannerHeight;
        imageView.setLayoutParams(bannerLayoutParams);

        new LoadImage.builder(LoadImage.bind(mData.get(position)), imageView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        cardView.setMaxCardElevation(mBaseElevation * MAX_ELEVATION_FACTOR);
        mViews.set(position, cardView);
        return view;
    }

    @Override
    public void destroyItem(ViewGroup container, int position, Object object) {
        container.removeView((View) object);
        mViews.set(position, null);
    }
}
