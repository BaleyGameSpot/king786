package ViewPagerCards;

import android.content.Context;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.core.widget.NestedScrollView;
import androidx.viewpager.widget.PagerAdapter;

import com.act.AppLoginActivity;
import com.buddyverse.providers.R;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Objects;

public class CardImagePagerAdapter extends PagerAdapter implements CardAdapter {

    private Context mContext;
    private final ArrayList<HashMap<String, String>> imageList = new ArrayList<>();
    private final List<CardView> mViews = new ArrayList<>();
    private float mBaseElevation;
    private final int bannerWidth, bannerHeight;

    public CardImagePagerAdapter(Context context) {
        this.mContext = context;

        bannerWidth = (int) (Utils.getScreenPixelWidth(context) - context.getResources().getDimensionPixelSize(R.dimen._30sdp));
        bannerHeight = (int) (bannerWidth / 1.33333333333);

        if (context instanceof AppLoginActivity) {
            View imgWelcomeImgContainer = ((AppLoginActivity) context).findViewById(R.id.imgWelcomeImgContainer);

            LinearLayout.LayoutParams lyParams = (LinearLayout.LayoutParams) imgWelcomeImgContainer.getLayoutParams();
            lyParams.height = bannerHeight + context.getResources().getDimensionPixelSize(R.dimen._65sdp);
            imgWelcomeImgContainer.setLayoutParams(lyParams);
        }
    }

    public void addCardItem(HashMap<String, String> item, Context context) {
        mViews.add(null);
        imageList.add(item);
        this.mContext = context;
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
        return imageList.size();
    }

    @Override
    public boolean isViewFromObject(@NonNull View view, @NonNull Object object) {
        return view == object;
    }

    @NonNull
    @Override
    public Object instantiateItem(ViewGroup container, int position) {
        View view = LayoutInflater.from(container.getContext()).inflate(R.layout.img_adapter, container, false);
        container.addView(view);

        ImageView imageView = view.findViewById(R.id.imagView);

        RelativeLayout.LayoutParams lyImgParams = (RelativeLayout.LayoutParams) imageView.getLayoutParams();
        lyImgParams.width = bannerWidth;
        lyImgParams.height = bannerHeight;
        imageView.setLayoutParams(lyImgParams);

        MTextView TitleTxt = view.findViewById(R.id.TitleTxt);
        MTextView subTitleTxt = view.findViewById(R.id.subTitleTxt);
        TitleTxt.setText(imageList.get(position).get("tTitle"));
        subTitleTxt.setText(imageList.get(position).get("tSubtitle"));

        position = position % mViews.size();
        String img = Utils.getResizeImgURL(mContext, Objects.requireNonNull(imageList.get(position).get("vImage")), 0, bannerHeight, bannerWidth);
        Logger.d("BannerImageSize", "::" + img);
        new LoadImage.builder(LoadImage.bind(img), imageView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

        View shadowHeaderView = view.findViewById(R.id.shadowHeaderView);
        shadowHeaderView.setVisibility(View.INVISIBLE);

        NestedScrollView scroller = view.findViewById(R.id.nestedScrollView);
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (scroller.getChildAt(0).getMeasuredHeight() >= (scroller.getMeasuredHeight())) {
                shadowHeaderView.setVisibility(View.VISIBLE);
            }
        }, 50);

        scroller.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {

            if (scrollY > oldScrollY) {
                shadowHeaderView.setVisibility(View.VISIBLE);
            }
            if (scrollY < oldScrollY) {
                shadowHeaderView.setVisibility(View.VISIBLE);
            }

            if ((scrollY - mContext.getResources().getDimensionPixelSize(R.dimen._15sdp)) == Math.round((v.getChildAt(0).getMeasuredHeight() - (v.getMeasuredHeight())))) {
                shadowHeaderView.setVisibility(View.INVISIBLE);
            }
        });
        return view;
    }

    @Override
    public void destroyItem(ViewGroup container, int position, @NonNull Object object) {
        container.removeView((View) object);
        mViews.set(position, null);
    }
}