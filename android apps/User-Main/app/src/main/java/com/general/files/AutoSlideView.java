package com.general.files;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager.widget.ViewPager;

import java.util.Objects;
import java.util.Timer;
import java.util.TimerTask;

public class AutoSlideView {

    private final long delaySec;

    private TimerTask timerTask;
    private Timer timer;
    public int nextPosition = 0;

    public AutoSlideView(int delaySec) {
        this.delaySec = delaySec;
    }

    public void removeAll() {
        nextPosition = 0;
        if (timer != null) {
            timer.cancel();
        }
        timer = null;
        if (timerTask != null) {
            timerTask.cancel();
        }
        timerTask = null;
    }

    public void setAutoSlideRV(@NonNull RecyclerView rvView) {
        if (timerTask == null) {
            timerTask = new TimerTask() {
                @Override
                public void run() {
                    if (rvView.getAdapter() == null) {
                        return;
                    }
                    nextPosition++;
                    if (nextPosition >= rvView.getAdapter().getItemCount()) {
                        nextPosition = 0;
                    }
                    rvView.smoothScrollToPosition(nextPosition);
                }
            };
            rvView.addOnScrollListener(
                    new RecyclerView.OnScrollListener() {
                        @Override
                        public void onScrollStateChanged(@NonNull RecyclerView recyclerView, int newState) {
                            super.onScrollStateChanged(recyclerView, newState);
                            if (newState == RecyclerView.SCROLL_STATE_IDLE) {
                                nextPosition = ((LinearLayoutManager) Objects.requireNonNull(rvView.getLayoutManager())).findFirstVisibleItemPosition();
                            }
                        }

                        @Override
                        public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                            super.onScrolled(recyclerView, dx, dy);
                        }
                    }
            );
        }
        if (timer == null) {
            timer = new Timer();
            timer.schedule(timerTask, delaySec, delaySec);
        }
    }

    public void setAutoSlidePageView(@NonNull ViewPager pageView) {
        if (timerTask == null) {
            timerTask = new TimerTask() {
                @Override
                public void run() {
                    MyApp.getInstance().getCurrentAct().runOnUiThread(() -> {
                        if (pageView.getAdapter() == null) {
                            return;
                        }
                        nextPosition++;
                        if (nextPosition >= pageView.getAdapter().getCount()) {
                            nextPosition = 0;
                        }
                        pageView.setCurrentItem(nextPosition, true);
                    });
                }
            };

            pageView.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
                @Override
                public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
                }

                @Override
                public void onPageSelected(int position) {
                    nextPosition = position;
                }

                @Override
                public void onPageScrollStateChanged(int state) {
                }
            });
        }
        if (timer == null) {
            timer = new Timer();
            timer.schedule(timerTask, delaySec, delaySec);
        }
    }
}